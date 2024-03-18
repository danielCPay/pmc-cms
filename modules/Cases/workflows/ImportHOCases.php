<?php

require 'modules/Documents/workflows/ImportClaims.php';
require_once 'include/main/WebUI.php';


use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Color;

class analizator_case
{
    public array $translacje;

    static function testfield( string $kom, array &$nag, string $exn, string $fn, string $ng)
    {
        if ( $exn == $kom)
        {
            $nag[$fn][1] = $nag[$fn][1] ?? null ? : $ng;
            return [ true, $fn];
        }
        return false;
    }
    function wykryj( string $k, array &$nag, ?string $xv) : ?array
    {
        $k = strtolower( $k);

        if ( $rt = self::testfield( $k, $nag, 'insured', 'insured_name', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insuredphonenumber', 'ins_phone', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'street', 'ins_street', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'address', 'ins_street', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'zip', 'ins_zip', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'state', 'ins_state', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'city', 'ins_city', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'typeofclaim', 'type_of_claim', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'stage', 'stage', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'status', 'cs_status', $xv)) { return $rt;}
        elseif ( strstr( $k, 'email'))
        {
            $nag['insured_e_mail'][1] = $nag['ins_e_mail'][1] ?? null ?: $xv;
            $nag['insured_e_mail'][0] = NumberFormat::FORMAT_TEXT;
            return [false, 'set_insured_e_mail'];
        }
        elseif ( str_starts_with( $k, "claimn"))
        {
            $nag['nu_claim'][1] = $nag['nu_claim'][1] ?? null ?: $xv;
            $nag['nu_claim'][0] = NumberFormat::FORMAT_TEXT;
            return [false, 'set_nu_claim'];
        }
        elseif ( str_starts_with( $k, "policyn"))
        {
            $nag['nu_policy'][1] = $nag['nu_policy'][1] ?? null ?: $xv;
            $nag['nu_policy'][0] = NumberFormat::FORMAT_TEXT;
            return [false, 'set_nu_policy'];
        }
        elseif ( str_starts_with( $k, 'insuranceco'))
        {
            $nag['insurance_company'][1] = $nag['insurance_company'][1] ?? null ?: $xv;
            $nag['insurance_company'][0] = NumberFormat::FORMAT_TEXT;
            return [true, 'insurance_company'];
        }
        elseif ( str_starts_with( $k, 'invoiceam'))
        {
            $nag['invoice_value'][1] = $nag['invoice_value'][1] ?? null ?: $xv;
            $nag['invoice_value'][0] = NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
            $nag['invoice_name'][1] = 'Invoice Name';
            $nag['invoice_name'][0] = NumberFormat::FORMAT_TEXT;
            return [false, 'set_invoice_value'];
        }
        elseif ( $k == 'dateofloss')
        {
            $nag['dol'][1] = $nag['dol'][1] ?? null ?: $xv;
            $nag['dol'][0] = NumberFormat::FORMAT_DATE_XLSX14;
            return [false, 'set_dol'];
        }

        return null;
    }
    function analizuj( int $kol, ?string $nazwa, array &$nag, array &$pstNag, ?string $xv)
    {
        if (empty($nazwa))
        {
            $this->translacje[$kol] = null;
            return;
        }

        $ret = $this->wykryj( $nazwa, $nag, $xv);
        $this->translacje[] = $ret;
        if ( !isset( $ret))
            $pstNag[] = $nazwa;
    }
    function ustaw( $cl, int $kol, $val)
    {
        $tr = $this->translacje[$kol];
        if ( is_null( $tr) || empty( $val))
            return;

        if ( strstr( $tr[1], 'name'))
            $val = claim_in_xls_row::normalizuj_name( $val);

        if ( $tr[0] == true)
        {
            $pole = $tr[1];
            if ( empty( $cl->$pole))
                $cl->$pole = $val;

            return;
        }

        if ( isset( $cl->wywolane[$tr[1]]))
            return;

        call_user_func(array($cl, $tr[1]), $val);
    }
}
class case_in_xls_row
{
    const   OK = 0;
    const   MOD = 1;
    const   POWTORZ = 2;
    const   BLAD_KOL = 3;
    const   BLAD_REK = 4;

    public array $kolumny;
    public array $faktury;
    public array $wywolane;
    public string $status_kol = "";
    public string $status_row = "";

    public function __construct( int $r)
    {
        $this->rz = $r;
    }

    function czy_ten_sam_case(case_in_db $dc) : bool
    {
        $ins = new insured_in_db();
        $ins->name = $this->insured_name;
        $ins->street = $this->ins_street;

        if ( ( ( $this->nu_claim ?? null) == $dc->claim_number) &&
            ( ( $this->nu_policy ?? null) == $dc->policy_number) &&
            ( $ins->get_key() == $dc->insured->get_key()))
        {
            return true;
        }

        return false;
    }
    function czy_duplikat(case_in_db $dc) : bool
    {
        if ( $this->czy_ten_sam_case( $dc))
        {
            if ( !isset( $dc->faktury))
                return false;
            foreach ( $dc->faktury as $cf)
                foreach ( $this->faktury as $xf)
                    if ( $xf->wartosc == $cf->wartosc && $xf->nazwa == $cf->nazwa)
                        return true;
        }

        return false;
    }
    function sprawdz_kolumny(case_in_db $cl) : int
    {
        if ( strtolower( $this->insured_name ?? null) != strtolower( $cl->insured->name))
        {
            $this->kolumny['insured_name'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Diff. " . ImportHOCases::$naglowki['insured_name'][1] . " with existing case " . $cl->case_number);
        }
        if ( ( $this->ins_street ?? null) != $cl->insured->street)
        {
            $this->kolumny['ins_street'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Diff. " . ImportHOCases::$naglowki['ins_street'][1] . " with existing insured " . $cl->insured->name);
        }
        if ( !empty( $cl->insured->e_mail) && (( $this->ins_e_mail ?? null) != $cl->insured->e_mail))
        {
            $this->kolumny['insured_e_mail'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Diff. " . ImportHOCases::$naglowki['insured_e_mail'][1] . " with existing case " . $cl->case_number);
        }
        elseif ( ( $this->insurance_company ?? null) != $cl->insurance_company)
        {
            stat_append( $this->status_kol, "Diff. " . ImportHOCases::$naglowki['insurance_company'][1] . " with existing " . $cl->case_number);
            $this->kolumny['insurance_company'][1] = self::BLAD_KOL;
        }
        foreach ( $this->faktury as $f)
            if (round($f->wartosc, 2) != $f->wartosc)
            {
                $f->wartosc = round($f->wartosc, 2);
                stat_append($this->status_kol, "Too big precision for invoice value");
            }

        return $this->status;
    }
    function sprawdz_obowiazkowe()
    {
        $this->kolumny['insured_name'][0] = $this->insured_name ?? null;
        $this->kolumny['insurance_company'][0] = $this->insurance_company ?? null;
        $this->kolumny['insured_e_mail'][0] = $this->ins_e_mail ?? null;
        $this->kolumny['nu_policy'][0] = $this->nu_policy ?? null;
        $this->kolumny['nu_claim'][0] = $this->nu_claim ?? null;
        $this->kolumny['type_of_claim'][0] = $this->type_of_claim ?? null;
        $this->kolumny['dol'][0] = $this->dol ?? null;
        $this->kolumny['ins_state'][0] = $this->ins_state ?? null;
        $this->kolumny['ins_zip'][0] = $this->ins_zip ?? null;
        $this->kolumny['ins_phone'][0] = $this->ins_phone ?? null;
        $this->kolumny['ins_city'][0] = $this->ins_city ?? null;
        $this->kolumny['ins_street'][0] = $this->ins_street ?? null;
        $this->kolumny['stage'][0] = $this->stage ?? null;
        $this->kolumny['cs_status'][0] = $this->cs_status ?? null;

        if ( isset( $this->faktury))
            foreach ( $this->faktury as $f)
            {
                $this->kolumny['invoice_value'][0] = $f->wartosc;
                $this->kolumny['invoice_name'][0] = $f->nazwa;
            }

        $this->status = self::OK;

        if ( strlen( $this->insured_name ?? "") > 254)
        {
            $this->kolumny['insured_name'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Too long " . ImportHOCases::$naglowki['insured_name'][1]);
        }
        if ( empty( $this->insured_name))
        {
            $this->kolumny['insured_name'][1] = self::BLAD_KOL;
            $this->status = self::BLAD_REK;
            stat_append( $this->status_row, "Bad " . ImportHOCases::$naglowki['insured_name'][1] ?? "insured name");
        }
        if ( empty( $this->ins_e_mail))
        {
            $this->kolumny['insured_e_mail'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Bad " . ImportHOCases::$naglowki['insured_e_mail'][1] ?? "e-mail" . " format");
        }
        if ( empty( $this->ins_street))
        {
            $this->status = self::BLAD_REK;
            $this->kolumny['ins_street'][1] = self::BLAD_KOL;
            stat_append( $this->status_row, "Empty " . ( ImportHOCases::$naglowki['ins_street'][1] ?? "street"));
        }
        if ( empty( $this->nu_claim))
        {
            $this->status = self::BLAD_REK;
            $this->kolumny['nu_claim'][1] = self::BLAD_KOL;
            stat_append( $this->status_row, "Empty " . ( ImportHOCases::$naglowki['nu_claim'][1] ?? "Claim #"));
        }
        if ( empty( $this->nu_policy))
        {
            $this->status = self::BLAD_REK;
            $this->kolumny['nu_policy'][1] = self::BLAD_KOL;
            stat_append( $this->status_row, "Empty " . ( ImportHOCases::$naglowki['nu_policy'][1] ?? "Policy #"));
        }
        if ( empty( $this->insurance_company))
        {
            $this->status = self::BLAD_REK;
            $this->kolumny['insurance_company'][1] = self::BLAD_KOL;
            stat_append( $this->status_row, "Empty " . ( ImportHOCases::$naglowki['insurance_company'][1] ?? "Insurance co."));
        }
        if ( empty( $this->faktury))
        {
            $this->status = self::BLAD_REK;
            stat_append( $this->status_row, "Wrong " . ( ImportHOCases::$naglowki['invoice_value'][1] ?? "invoice"));
        }
        if ( empty( $this->type_of_claim) || $this->type_of_claim != "HO")
        {
            $this->kolumny['type_of_claim'][1] = self::BLAD_KOL;
            $this->status = self::BLAD_REK;
            stat_append( $this->status_row, "Empty or wrong " . ( ImportHOCases::$naglowki['type_of_claim'][1] ?? "Type of Claim"));
        }
        if ( empty( $this->dol) || DateTime::createFromFormat( "Y-m-d", $this->dol)->diff( date_create())->y>20)
        {
            $this->kolumny['dol'][1] = self::BLAD_KOL;
            $this->status = self::BLAD_REK;
            stat_append( $this->status_row, "Empty " . ( ImportHOCases::$naglowki['dol'][1] ?? "DOL"));
        }
        if ( empty( $this->stage))
        {
            $this->stage = "Pre-Litigation";
            $this->cs_status = "New Case Entered - HO";
        }
        switch ( $this->stage )
        {
            case "Pre-Litigation": return;
            case "Complaint" :  return;
            case "Plaintiff Discovery" :  return;
            case "Plaintiff Deposition" :  return;
            case "Defendant Discovery" :  return;
            case "Defendant Deposition" :  return;
            case "Mediation Arbitration" :  return;
            case "Plaintiff MSJ" :  return;
            case "Defendant MSJ" :  return;
            case "Trial" :  return;
            case "Settlement" :  return;
            case "Appeal" :  return;
            case "PFS CRN 57.105" :  return;

            default :
                $this->kolumny['stage'][1] = self::BLAD_KOL;
                $this->status = self::BLAD_REK;
                stat_append( $this->status_row, "Wrong stage value");
        }

    }
    public static function tofloat($num) : float {
        $dotPos = strrpos($num, '.');
        $commaPos = strrpos($num, ',');
        $sep = (($dotPos > $commaPos) && $dotPos) ? $dotPos :
            ((($commaPos > $dotPos) && $commaPos) ? $commaPos : false);

        if (!$sep) {
            return floatval(preg_replace("/[^0-9]/", "", $num));
        }

        return floatval(
            preg_replace("/[^0-9]/", "", substr($num, 0, $sep)) . '.' .
            preg_replace("/[^0-9]/", "", substr($num, $sep+1, strlen($num)))
        );
    }

    function    ustaw_date( String $s) : ?string
    {
        unset($ds);
        unset($znal);
        if ( preg_match( '/(202.)[\.\-\/\\\\](..)[\.\-\/\\\\](..)/',$s, $znal) == 1)
            $ds = $s;
        else
        if ( preg_match( '/(..)[\.\-\/\\\\](..)[\.\-\/\\\\](202.)/',$s, $znal) == 1)
            $ds = $znal[3] . '-' . $znal[1] . '-' . $znal[2];
        else
        if ( preg_match( '/(..)[\.\-\/\\\\](..)[\.\-\/\\\\](2.)/',$s, $znal) == 1)
            $ds = '20' . $znal[3] . '-' . $znal[1] . '-' . $znal[2];

        if ( isset( $znal[1]) && isset( $ds))
        {
            if ($znal[1] > 12)
                return null;

            return $ds;
        }

        if ( floatval( $s) > 10)
        {
            $myDateTime = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject( $s);
            return $myDateTime->format('Y-m-d');
        }

        return null;
    }
    function    set_invoice_value(String $s)
    {
        $this->wywolane[__FUNCTION__] = true;
        if ( ( $this->wywolane['set_fin_invoice_value'] ?? false) || ( $this->wywolane['set_ini_invoice_value'] ?? false))
            return;

        $f = new HOfaktura_w_db( 'Rebuild Estimate', $this->tofloat( $s), $this);
        $f->estimate_am = $this->inv_est ?? null;

        $this->faktury[] = $f;
    }
    static public function    normalizuj_name( String $s) : string
    {
        $s = str_replace( " and ", " & ", $s);
        $s = str_replace( " AND ", " & ", $s);
        $s = str_replace( " &amp; ", " & ", $s);
        $s = str_replace( ",", " & ", $s);
        $s = preg_replace('/\s+/', ' ', $s);

        return $s;
    }
    function    set_insured_e_mail( String $s)
    {
        if ( !filter_var($s, FILTER_VALIDATE_EMAIL))
            return;

        $this->wywolane[__FUNCTION__] = true;

        $this->ins_e_mail = strtolower( $s);
    }
    function    set_dol( String $s)
    {
        $f = $this->ustaw_date( $s);
        if ( !isset( $f))
            return;

        $this->wywolane[__FUNCTION__] = true;

        $this->dol = $f;
    }
    function    set_nu_policy( String $s)
    {
        $this->wywolane[__FUNCTION__] = true;

        $this->nu_policy = preg_replace( '/\s+/u', '', $s);
    }
    function    set_nu_claim( String $s)
    {
        $this->wywolane[__FUNCTION__] = true;

        $this->nu_claim = preg_replace( '/\s+/u', '', $s);
    }
}
class ImportHOCases
{
    public array $xlCases;
    static public insuredsDb $dBinsL;
    static public insurance_coDb $dBincoL;
    public casesDb $dBcasL;
    public HOfakturaDb $dBinvl;
    static public array $naglowki;
    public \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet;

    public function zaladuj_db()
    {
        self::$dBinsL = new insuredsDb();
        self::$dBinsL->zaladuj_ins();
        self::$dBincoL = new insurance_coDb();
        self::$dBincoL->zaladuj();
        $this->dBcasL = new casesDb();
        $this->dBcasL->zaladuj();
        $this->dBinvl = new HOfakturaDb();
        $this->dBinvl->zaladuj( $this->dBcasL);
    }
    public static function import_cases_from_excel( Vtiger_Record_Model $recordModel)
    {
        \App\Log::warning( 'ImportHOCases::import_cases_from_excel');

        $path = $recordModel->getFileDetails()['path'];
        $fn = $recordModel->get('filename');
        $at = $recordModel->getFileDetails()['attachmentsid'];

        \App\Log::warning( 'ImportHOCases:' . " path: " . $path . " fn: " . $fn . " at: " . $at);

        $m = new ImportHOCases();
        try
        {
            $m->zaladuj_db();
            $m->czytaj( $path . $at);
            $m->utworz_nowe_casey_w_pam();
            $m->utworz_nowe_casey_w_db();
            $w = new nowy_Excel($m->xlCases, ImportHOCases::$naglowki, $m->spreadsheet);
            $status = $w->zapisz_Xlsx( $path . $at);
        }
        catch ( Throwable $e)
        {
            \App\Log::error( $e);
            $status = "Serious error";
        }

        \App\Log::warning( 'ImportHOCases::status : ' . $status);
        $recordModel->set( 'filename', 'rep_' . $fn);
        $recordModel->set( 'verification_warnings', $status);
        $recordModel->save();
    }
    public function utworz_nowe_casey_w_pam()
    {
        $cases = [];
        foreach ($this->xlCases as $xc)
        {
            if ( $xc->status == case_in_xls_row::BLAD_REK)
                continue;

            $zlc = $cases[$xc->nu_claim . $xc->nu_policy] ?? $this->dBcasL->cases[$xc->nu_claim . $xc->nu_policy] ?? null;
            unset( $c);
            if ( $zlc != null)
            {
                if ( $xc->sprawdz_kolumny( $zlc[0]) == case_in_xls_row::BLAD_REK)
                    continue;
                foreach ( $zlc as $zc)
                {
                    if ($xc->czy_duplikat( $zc))
                    {
                        $xc->status = case_in_xls_row::POWTORZ;
                        goto next_xc;
                    }
                    if ( ( $zc->zXl ?? false) && $xc->czy_ten_sam_case( $zc))
                    {
                        $c = true;
                        $zc->faktury = array_merge( $zc->faktury, $xc->faktury);
                        goto next_xc;
                    }
                }
            }

            if ( !isset( $c))
            {
                $nc = new case_in_db();
                $nc->init_zXl($xc);
                $cases[ $xc->nu_claim . $xc->nu_policy][] = $nc;
            }

            next_xc:
        }

        foreach ( array_keys( $cases) as $k)
        {
            $zl = $this->dBcasL->cases[$k] ?? null;
            if ( isset( $zl))
                $this->dBcasL->cases[$k] = array_merge( $zl, $cases[$k]);
            else
                $this->dBcasL->cases[$k] = $cases[$k];
        }
    }
    public function utworz_nowe_casey_w_db()
    {
        foreach ($this->dBcasL->cases as $cli)
            foreach ($cli as $dc)
                if ( isset( $dc->zXl))
                    try
                    {
                        $dc->zapisz_do_db();
                    }
                    catch( Throwable $ex)
                    {
                        for( $i = 0; $i < sizeof( $dc->faktury); $i++)
                        {
                            $dc->faktury[$i]->xcl->status = case_in_xls_row::BLAD_REK;
                            stat_append($dc->faktury[$i]->xcl->status_row, quoted_printable_encode( $ex->getMessage()));
                        }
                    }
    }
    public function czytaj( string $plikWej)
    {
        $an = new analizator_case();
        $reader = new Xlsx();
        try
        {
            $this->spreadsheet = $reader->load($plikWej);
        }
        catch ( Exception $ex)
        {
            throw new Exception("Problem reading file " . $plikWej);
        }

        $ws = $this->spreadsheet->setActiveSheetIndex( 0);

        $rzi = 1;
        self::$naglowki = [];
        ImportClaims::$pusteNaglowki = [];
        foreach ( $ws->getRowIterator() as $rz )
        {
            $koli = 0;
            $c = new case_in_xls_row( $rzi);
            if ($rzi++ == 1)
            {
                foreach ($rz->getCellIterator() as $kom)
                {
                    $k = preg_replace("/\s+/u", "", $kom->getValue());
                    $k = str_replace( '-','', $k);
                    $k = trim( $k);
                    $an->analizuj( $koli++, $k, self::$naglowki, ImportClaims::$pusteNaglowki, $kom->getValue());
                }
                continue;
            }
            else
            {
                foreach ($rz->getCellIterator() as $kom)
                {
                    $k = preg_replace("/[\s\x{200C}\x{200B}]+/u", " ", $kom->getValue());
                    $k = trim($k);
                    $an->ustaw( $c, $koli++, $k);
                }
            }
            $c->sprawdz_obowiazkowe();
            $this->xlCases[] = $c;
        }
    }
}
class HOfaktura_w_db
{
    public string  $nazwa;
    public float  $wartosc;
    public  $xcl;
    static int $fl = 0;

    public function __construct( string $n, float $w, case_in_xls_row &$c = null)
    {
        $this->nazwa = $n;
        $this->wartosc = $w;
        $this->xcl = $c;
    }
    public function zapisz_w_db( case_in_db $ca)
    {
        $cldb = Vtiger_Record_Model::getCleanInstance('HOClaimedInvoices');
        $cldb->set('ho_claimed_invoice_name', $this->nazwa);
        $cldb->set('invoice_value', $this->wartosc);
        $cldb->set('case', $ca->casesid);
        $cldb->save();
    }
}
class HOfakturaDb
{
    function zaladuj( casesDb $cd)
    {
        $qg = new \App\QueryGenerator( 'HOClaimedInvoices');
        $qg->setField( [ 'ho_claimed_invoice_name', 'invoice_value', 'case']);
        $qg->addRelatedField([ 'sourceField' => 'case', 'relatedModule' => 'Cases', 'relatedField' => 'claim_number' ]);
        $qg->addRelatedField([ 'sourceField' => 'case', 'relatedModule' => 'Cases', 'relatedField' => 'policy_number' ]);
        $q = $qg->createQuery();

        $rez = $q->all();
        foreach ( $rez as $pos)
        {
            $ca = $cd->cases[$pos['caseCasesclaim_number'] . $pos['caseCasespolicy_number']] ?? null;
            if ( !empty( $ca))
                foreach ( $ca as $c)
                    if ( $c->casesid == $pos['case'])
                        $c->faktury[] = new HOfaktura_w_db( $pos['ho_claimed_invoice_name'], $pos['invoice_value'] ?? 0);
        }
    }
}
class case_in_db
{
    public array $faktury;

    public function cs_ustaw_z_db( array $pos)
    {
        $this->casesid = $pos['id'];
        $this->insurance_company = $pos['insurance_companyInsuranceCompaniesinsurance_company_name'];
        $this->insured = new insured_in_db();
        $this->insured->name = case_in_xls_row::normalizuj_name( strtolower( $pos['insuredInsuredsinsured_name']));
        $this->insured->street = $pos['insuredInsuredsstreet'];
        $this->claim_number = $pos['claim_number'];
        $this->policy_number = $pos['policy_number'];
        $this->date_of_loss = $pos['date_of_loss'];
    }
    public function init_zXl( case_in_xls_row &$xc)
    {
        $this->zXl = true;

        $this->insured = new insured_in_db();
        $this->insured->ustaw_z_xl( $xc);
        $this->insurance_company = $xc->insurance_company;
        $this->claim_number = $xc->nu_claim;
        $this->policy_number = $xc->nu_policy;
        $this->date_of_loss = $xc->dol;
        $this->faktury = $xc->faktury;
        $this->type_of_claim = $xc->type_of_claim;
        $this->stage = $xc->stage;
        $this->cs_status = $xc->cs_status;
    }
    function zapisz_do_db()
    {
        $ms = ImportHOCases::$dBinsL->sprawdz_lub_stworz( $this);
        $ms2 = ImportHOCases::$dBincoL->sprawdz( $this);
        if ( isset( $ms))
            $this->faktury[0]->xcl->kolumny['insured_name'][1] = case_in_xls_row::BLAD_KOL;
        if ( isset( $ms2))
            $this->faktury[0]->xcl->kolumny['insurance_company'][1] = case_in_xls_row::BLAD_KOL;

        $cldb = Vtiger_Record_Model::getCleanInstance('Cases');
        $cldb->set('claim_number', $this->claim_number);
        $cldb->set('policy_number', $this->policy_number);
        $cldb->set('date_of_loss', $this->date_of_loss);
        $cldb->set('insured', $this->insured->id);
        $cldb->set('status', $this->cs_status);
        $cldb->set('stage', $this->stage);
        $cldb->set('type_of_claim', $this->type_of_claim);

        if ( isset( $this->insurance_company_id))
            $cldb->set('insurance_company', $this->insurance_company_id);

        $adj = 0;
        foreach ( $this->faktury as $f)
            $adj += $f->wartosc;

        $cldb->set('total_bill_amount', $adj);
        $cldb->save();

        $this->casesid = $cldb->get('id');

        stat_append( $this->faktury[0]->xcl->status_kol, $ms);
        stat_append( $this->faktury[0]->xcl->status_kol, $ms2);

        foreach ( $this->faktury as $f)
            $f->zapisz_w_db( $this);
    }
}
class casesDb
{
    public array $cases;

    function zaladuj()
    {
        $qg = new \App\QueryGenerator( 'Cases');
        $qg->setField( ['id', 'date_of_loss', 'insured', 'claim_number', 'policy_number']);
        $qg->addRelatedField([ 'sourceField' => 'insurance_company', 'relatedModule' => 'InsuranceCompanies', 'relatedField' => 'insurance_company_name' ]);
        $qg->addRelatedField([ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'insured_name', ]);
        $qg->addRelatedField([ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'street', ]);
        $qg->addCondition( 'case_id', "HOS%", 's');
        $q = $qg->createQuery();

        $rez = $q->all();
        $this->cases = [];

        foreach ( $rez as $pos)
        {
            $cl = new case_in_db();
            $cl->cs_ustaw_z_db( $pos);

            $cn = $pos['claim_number'] . $pos['policy_number'];
            if ( empty( $cl->policy_number))
                continue;
            $this->cases[$cn][] = $cl;
        }
    }
}
