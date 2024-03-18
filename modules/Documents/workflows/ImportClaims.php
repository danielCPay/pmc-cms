<?php

require_once 'claimsAttachements.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Color;

class AttorneyException extends Exception
{
    public function __construct($message, $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
function stat_append( string &$s1, ?string $s2)
{
    if ( empty( $s2))
        return;

    if ( !empty( $s1))
        $s1 .= PHP_EOL;
    $s1 .= $s2;
}

class analizator_claim
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

        if ( $rt = self::testfield( $k, $nag, 'insuredname', 'insured_name', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured', 'insured_name', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured1firstname', 'first_name_1', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured1lastname', 'last_name_1', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured2firstname', 'first_name_2', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured2lastname', 'last_name_2', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured3firstname', 'first_name_3', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured3lastname', 'last_name_3', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured4firstname', 'first_name_4', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insured4lastname', 'last_name_4', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insuredfirstname', 'first_name_1', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'insuredlastname', 'last_name_1', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'hoattorney', 'ho_attorney', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'holawfirm', 'ho_law_firm', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'prelitigationstatus', 'plst', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'courtcasenumber', 'court_case', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'countyofthecase', 'county_case', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'primaryphone', 'ins_phone', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'street', 'ins_street', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'address', 'ins_street', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'addressstreet', 'ins_street', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'zip', 'ins_zip', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'addresszip', 'ins_zip', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'state', 'ins_state', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'addressstate', 'ins_state', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'estimateamount(newfield)', 'inv_est', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'city', 'ins_city', $xv)) { return $rt;}
        elseif ( $rt = self::testfield( $k, $nag, 'addresscity', 'ins_city', $xv)) { return $rt;}
        else
            if ( str_contains( $k, 'attorney'))
            {
                $nag['pre_attorney'][1] = $nag['pre_attorney'][1] ?? null ?: $xv;
                $nag['pre_attorney'][0] = NumberFormat::FORMAT_TEXT;
                return [true, 'pre_attorney'];
            }
        else
            if ( strstr( $k, 'email'))
            {
                $nag['insured_e_mail'][1] = $nag['ins_e_mail'][1] ?? null ?: $xv;
                $nag['insured_e_mail'][0] = NumberFormat::FORMAT_TEXT;
                return [false, 'set_insured_e_mail'];
            }
        else
            if ( str_starts_with( $k, 'claim#') || str_starts_with( $k, "claimn"))
            {
                $nag['nu_claim'][1] = $nag['nu_claim'][1] ?? null ?: $xv;
                $nag['nu_claim'][0] = NumberFormat::FORMAT_TEXT;
                return [false, 'set_nu_claim'];
            }
        else
            if ( str_starts_with( $k, 'policy#') || str_starts_with( $k, "policyn"))
            {
                $nag['nu_policy'][1] = $nag['nu_policy'][1] ?? null ?: $xv;
                $nag['nu_policy'][0] = NumberFormat::FORMAT_TEXT;
                return [false, 'set_nu_policy'];
            }
        else
            if ( $k == 'typeofjob')
            {
                $nag['type_of_job'][1] = $nag['type_of_job'][1] ?? null ?: $xv;
                $nag['type_of_job'][0] = NumberFormat::FORMAT_TEXT;
                return [true, 'type_of_job'];
            }
        else
            if ( str_starts_with( $k, 'insuranceco'))
            {
                $nag['insurance_company'][1] = $nag['insurance_company'][1] ?? null ?: $xv;
                $nag['insurance_company'][0] = NumberFormat::FORMAT_TEXT;
                return [true, 'insurance_company'];
            }
        else
            if ( str_starts_with( $k, 'invoiceam'))
            {
                $nag['invoice_value'][1] = $nag['invoice_value'][1] ?? null ?: $xv;
                $nag['invoice_value'][0] = NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
                $nag['invoice_name'][1] = 'Invoice Name';
                $nag['invoice_name'][0] = NumberFormat::FORMAT_TEXT;
                return [false, 'set_invoice_value'];
            }
        else
            if ( str_starts_with( $k, 'finalinvoiceam'))
            {
                $nag['final_invoice'][1] = $nag['final_invoice'][1] ?? null ?: $xv;
                $nag['final_invoice'][0] = NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
                return [false, 'set_fin_invoice_value'];
            }
        else
            if ( str_starts_with( $k, 'initialinvoiceam'))
            {
                $nag['initial_invoice'][1] = $nag['initial_initial'][1] ?? null ?: $xv;
                $nag['initial_invoice'][0] = NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
                return [false, 'set_ini_invoice_value'];
            }
        else
            if ( str_starts_with( $k, 'loporaob') || $k == 'typeofclaim')
            {
                $nag['type_of_claim'][1] = $nag['type_of_claim'][1] ?? null ?: $xv;
                $nag['type_of_claim'][0] = NumberFormat::FORMAT_TEXT;
                return [false, 'set_lop_or_aob'];
            }
        elseif ( $k == 'dol')
            {
                $nag['dol'][1] = $nag['dol'][1] ?? null ?: $xv;
                $nag['dol'][0] = NumberFormat::FORMAT_DATE_XLSX14;
                return [false, 'set_dol'];
            }
        elseif ( $k == 'dos')
            {
                $nag['dos'][1] = $nag['dos'][1] ?? null ?: $xv;
                $nag['dos'][0] = NumberFormat::FORMAT_DATE_XLSX14;
                return [ false, 'set_dos'];
            }
        else if ( $k == 'dofn' || strstr( $k, "dateoffirstn"))
            {
                $nag['dofn'][1] = $nag['dofn'][1] ?? null ?: $xv;
                $nag['dofn'][0] = NumberFormat::FORMAT_DATE_XLSX14;
                return [ false, 'set_dofn'];
            }
        elseif ( $k == 'partialpayments' || $k == "partialpayment" || $k == 'priorcollections')
        {
            $nag['part_pay'][1] = $nag['part_pay'][1] ?? null ?: $xv;
            $nag['part_pay'][0] = NumberFormat::FORMAT_CURRENCY_USD_SIMPLE;
            return [ false, 'set_partial_pay'];
        }

        return null;
    }
    function analizuj( int $kol, ?string $nazwa, array &$nag, array &$pstNag, ?string $xv)
    {
        if ( claimsAttachements::$instance_->czyNaglowekAtt( $kol, $xv))
        {
            $this->translacje[$kol] = null;
            $pstNag[] = $nazwa;
            return;
        }
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
    function ustaw( claim_in_xls_row $cl, int $kol, $val)
    {
        if ( claimsAttachements::$instance_->czyKolumnaAtt( $cl->rz, $kol, $val))
            return;

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
class claim_in_xls_row
{
    const   OK = 0;
    const   MOD = 1;
    const   POWTORZ = 2;
    const   BLAD_KOL = 3;
    const   BLAD_REK = 4;
    const   TERMINATED = 5;

    public array $kolumny;
    public int $status;
    public string $status_kol = "";
    public string $status_row = "";
    public int $rz;
    public ?string $insured_name;
    public ?string $type_of_job;
    public ?string $insurance_company;
    public ?string $ho_law_firm;
    public ?string $ho_attorney;
    public ?string $pre_attorney;
    public ?string $nu_claim;
    public ?string $nu_policy;
    public ?string $type_of_claim;
    public ?string $dol;
    public ?string $dos;
    public ?string $dofn;
    public ?string $county_case;
    public ?string $court_case;
    public ?string $inv_est;
    public array $faktury;
    public array $wywolane;
    public string $first_name_1;
    public string $last_name_1;
    public string $first_name_2;
    public string $last_name_2;
    public string $first_name_3;
    public string $last_name_3;
    public string $first_name_4;
    public string $last_name_4;
    public string $ins_e_mail;
    public string $ins_street;
    public string $ins_city;
    public string $ins_state;
    public string $ins_zip;
    public string $ins_phone;
    public string $plst;
    public float $part_pay;

    public function __construct( int $r)
    {
        $this->rz = $r;
    }

    function czy_ten_sam_claim(claim_in_db $dc) : bool
    {
        if (
            ( attorneysDb::normalizuj( $this->nu_policy ?? null) == attorneysDb::normalizuj( $dc->policy_number)) &&
            ( attorneysDb::normalizuj( $this->type_of_claim ?? null) == attorneysDb::normalizuj( $dc->type_of_claim)) &&
            ( attorneysDb::normalizuj( $this->ins_street ?? null) == attorneysDb::normalizuj( $dc->insured->street)))
        {
            return true;
        }

        return false;
    }
    function czy_duplikat(claim_in_db $dc) : bool
    {
        if ( $this->czy_ten_sam_claim( $dc))
        {
            if ( !isset( $dc->faktury))
                return false;
            foreach ( $dc->faktury as $cf)
                foreach ( $this->faktury as $xf)
                    if ( $xf->wartosc == $cf->wartosc && $xf->nazwa == $cf->nazwa && $xf->prior_coll == $cf->prior_coll)
                        return true;
        }

        return false;
    }
    function sprawdz_kolumny(claim_in_db $cl) : int
    {
        if ( strtolower( $this->insured_name ?? null) != strtolower( $cl->insured->name))
        {
            $this->kolumny['insured_name'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Diff. " . ImportClaims::$naglowki['insured_name'][1] . " with existing claim " . $cl->claim_number);
        }
        if ( $cl->insured->street && ( attorneysDb::normalizuj( $this->ins_street ?? null) != attorneysDb::normalizuj( $cl->insured->street)))
        {
            $this->kolumny['ins_street'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Diff. " . ImportClaims::$naglowki['ins_street'][1] . " with existing claim " . $cl->claim_number);
        }
        if ( $cl->insured->e_mail && (( $this->ins_e_mail ?? null) != $cl->insured->e_mail))
        {
            $this->kolumny['insured_e_mail'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Diff. " . ImportClaims::$naglowki['insured_e_mail'][1] . " with existing claim " . $cl->claim_number);
        }
        if ( ( $this->nu_policy ?? null) != $cl->policy_number)
        {
            stat_append( $this->status_row, "Diff. " . ImportClaims::$naglowki['nu_policy'][1] . " with existing claim " . $cl->claim_number);
            $this->kolumny['nu_policy'][1] = self::BLAD_KOL;
            $this->status = self::BLAD_REK;
        }
        if ( strtolower( $this->insurance_company ?? null) != strtolower( $cl->insurance_company))
        {
            stat_append( $this->status_kol, "Diff. " . ImportClaims::$naglowki['insurance_company'][1] . " with existing " . $cl->insurance_company);
            $this->kolumny['insurance_company'][1] = self::BLAD_KOL;
        }
        if ( ( $this->ho_law_firm ?? null) != $cl->ho_law_firm)
        {
            stat_append( $this->status_kol, "Diff. Law firm with existing " . $cl->ho_law_firm);
            $this->kolumny['ho_law_firm'][1] = self::BLAD_KOL;
        }
        if ( ( $this->ho_attorney ?? null) != $cl->ho_attorney)
        {
            stat_append( $this->status_kol, "Diff. attorney with existing " . $cl->ho_attorney);
            $this->kolumny['ho_attorney'][1] = self::BLAD_KOL;
        }
        if ( ( $this->pre_attorney ?? null) != $cl->pre_attorney)
        {
            stat_append( $this->status_kol, "Diff. pre-attorney with existing " . $cl->pre_attorney);
            $this->kolumny['pre_attorney'][1] = self::BLAD_KOL;
        }
        foreach ( $this->faktury as $f)
        {
            if (round($f->wartosc, 2) != $f->wartosc)
            {
                $f->wartosc = round($f->wartosc, 2);
                stat_append($this->status_kol, "Too big precision for invoice value");
            }
            if ( isset( $f->prior_coll))
                if (round($f->prior_coll, 2) != $f->prior_coll)
                {
                    $f->prior_coll = round($f->prior_coll, 2);
                    stat_append($this->status_kol, "Too big precision for prior coll.");
                }
        }

        return $this->status;
    }
    function sprawdz_obowiazkowe()
    {
        $this->kolumny['insured_name'][0] = $this->insured_name ?? null;
        $this->kolumny['insured_e_mail'][0] = $this->ins_e_mail ?? null;
        $this->kolumny['type_of_job'][0] = $this->type_of_job ?? null;
        $this->kolumny['insurance_company'][0] = $this->insurance_company ?? null;
        $this->kolumny['ho_law_firm'][0] = $this->ho_law_firm ?? null;
        $this->kolumny['ho_attorney'][0] = $this->ho_attorney ?? null;
        $this->kolumny['nu_claim'][0] = $this->nu_claim ?? null;
        $this->kolumny['nu_policy'][0] = $this->nu_policy ?? null;
        $this->kolumny['type_of_claim'][0] = $this->type_of_claim ?? null;
        $this->kolumny['dol'][0] = $this->dol ?? null;
        $this->kolumny['dos'][0] = $this->dos ?? null;
        $this->kolumny['ins_state'][0] = $this->ins_state ?? null;
        $this->kolumny['ins_zip'][0] = $this->ins_zip ?? null;
        $this->kolumny['ins_phone'][0] = $this->ins_phone ?? null;
        $this->kolumny['ins_city'][0] = $this->ins_city ?? null;
        $this->kolumny['ins_street'][0] = $this->ins_street ?? null;
        $this->kolumny['first_name_1'][0] = $this->first_name_1 ?? null;
        $this->kolumny['last_name_1'][0] = $this->last_name_1 ?? null;
        $this->kolumny['first_name_2'][0] = $this->first_name_2 ?? null;
        $this->kolumny['last_name_2'][0] = $this->last_name_2 ?? null;
        $this->kolumny['first_name_3'][0] = $this->first_name_3 ?? null;
        $this->kolumny['last_name_3'][0] = $this->last_name_3 ?? null;
        $this->kolumny['first_name_4'][0] = $this->first_name_4 ?? null;
        $this->kolumny['last_name_4'][0] = $this->last_name_4 ?? null;
        $this->kolumny['part_pay'][0] = $this->part_pay ?? null;
        $this->kolumny['dofn'][0] = $this->dofn ?? null;
        $this->kolumny['plst'][0] = $this->plst ?? null;
        $this->kolumny['court_case'][0] = $this->court_case ?? null;
        $this->kolumny['county_case'][0] = $this->county_case ?? null;
        $this->kolumny['pre_attorney'][0] = $this->pre_attorney ?? null;

        if ( isset( $this->faktury))
        foreach ( $this->faktury as $f)
        {
            if ( str_starts_with( $f->nazwa, "FINAL"))
                $this->kolumny['final_invoice'][0] = $f->wartosc;
            elseif ( str_starts_with( $f->nazwa, "INITIAL"))
                $this->kolumny['initial_invoice'][0] = $f->wartosc;
            else
            {
                $this->kolumny['invoice_value'][0] = $f->wartosc;
                $this->kolumny['invoice_name'][0] = $f->nazwa;
            }
        }

        $this->status = self::OK;

        if ( empty( $this->insured_name))
        {
            if ( empty( $this->last_name_1) || empty( $this->first_name_1))
            {
                $this->status = self::BLAD_REK;
                $this->kolumny['insured_name'][1] = self::BLAD_KOL;
                stat_append($this->status_row, "Empty " . ( ImportClaims::$naglowki['insured_name'][1] ?? "Insured name"));
                return;
            }

            $this->insured_name = $this->first_name_1 . " " . $this->last_name_1;
        }

        $p = strpos( $this->insured_name, '(');
        $k = strpos( $this->insured_name, ')');
        $s1 = $p ? substr( $this->insured_name, 0, $p) : $this->insured_name;
        $s2 = $k ?substr( $this->insured_name, $k + 1) : null;
        $this->insured_name = trim( preg_replace( '/\s+/' , ' ', $s1 . $s2));
        $this->kolumny['insured_name'][0] = $this->insured_name;

        if ( strlen( $this->insured_name) > 254)
        {
            $this->kolumny['insured_name'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Too long " . ImportClaims::$naglowki['insured_name'][1]);
        }

        if ( empty( $this->ins_e_mail))
        {
            $this->kolumny['insured_e_mail'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Bad " . ImportClaims::$naglowki['insured_e_mail'][1] ?? "e-mail" . " format");
        }
        if ( empty( $this->nu_claim))
        {
            $this->status = self::BLAD_REK;
            $this->kolumny['nu_claim'][1] = self::BLAD_KOL;
            stat_append( $this->status_row, "Empty " . ( ImportClaims::$naglowki['nu_claim'][1] ?? "Claim #"));
        }
        if ( empty( $this->ho_attorney) && empty( $this->pre_attorney))
        {
            $this->status = self::BLAD_REK;
            if (empty($this->ho_law_firm))
                $this->kolumny['ho_law_firm'][1] = self::BLAD_KOL;
            if (empty($this->ho_attorney))
                $this->kolumny['ho_attorney'][1] = self::BLAD_KOL;
            if (empty($this->pre_attorney))
                $this->kolumny['pre_attorney'][1] = self::BLAD_KOL;

            $this->kolumny['attorneyorlw'][1] = self::BLAD_KOL;
            stat_append($this->status_row, "Empty " . (ImportClaims::$naglowki['attorneyorlw'][1] ?? "Attorney or Law firm"));
        }
        /*
        if ( empty( $this->ins_street))
        {
            $this->status = self::BLAD_REK;
            $this->kolumny['ins_street'][1] = self::BLAD_KOL;
            stat_append( $this->status_row, "Empty " . ( ImportClaims::$naglowki['ins_street'][1] ?? "street"));
        }
        */
        if ( empty( $this->nu_policy))
        {
            $this->status = self::BLAD_REK;
            $this->kolumny['nu_policy'][1] = self::BLAD_KOL;
            stat_append( $this->status_row, "Empty " . ( ImportClaims::$naglowki['nu_policy'][1] ?? "Policy #"));
        }
        if ( empty( $this->type_of_job))
            $this->kolumny['type_of_job'][1] = self::BLAD_KOL;
        if ( empty( $this->insurance_company))
        {
            $this->status = self::BLAD_REK;
            $this->kolumny['insurance_company'][1] = self::BLAD_KOL;
            stat_append( $this->status_row, "Empty " . ( ImportClaims::$naglowki['insurance_company'][1] ?? "Insurance co."));
        }
        if ( empty( $this->faktury))
        {
            $this->status = self::BLAD_REK;
            stat_append( $this->status_row, "Wrong " . ( ImportClaims::$naglowki['invoice_value'][1] ?? "invoice"));
        }
        if ( empty( $this->type_of_claim))
        {
            $this->kolumny['type_of_claim'][1] = self::BLAD_KOL;
            stat_append( $this->status_kol, "Empty Type of Claim");
        }
        if ( empty( $this->dos))
            $this->kolumny['dos'][1] = self::BLAD_KOL;
        if ( empty( $this->dol))
            $this->kolumny['dol'][1] = self::BLAD_KOL;


        attorneysDb::$instance_->sprawdz_zXl( $this);
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
    function    set_dofn( String $s)
    {
        $f = $this->ustaw_date( $s);
        if ( !isset( $f))
            return;

        $this->wywolane[__FUNCTION__] = true;

        $this->dofn = $f;
    }
    function    set_dos( String $s)
    {
        $f = $this->ustaw_date( $s);
        if ( !isset( $f))
            return;

        $this->wywolane[__FUNCTION__] = true;

        $this->dos = $f;
    }
    function    set_dol( String $s)
    {
        $f = $this->ustaw_date( $s);
        if ( !isset( $f))
            return;

        $this->wywolane[__FUNCTION__] = true;

        $this->dol = $f;
    }
    function    set_partial_pay( String $s)
    {
        $this->wywolane[__FUNCTION__] = true;

        if ( !empty( $this->faktury))
            end( $this->faktury)->prior_coll = $this->tofloat( $s);

        $this->part_pay = $this->tofloat( $s);
    }
    function    set_ini_invoice_value( String $s)
    {
        $this->wywolane[__FUNCTION__] = true;

        $f = new faktura_w_db( "INITIAL " . ( $this->type_of_job ?? ""), $this->tofloat( $s), $this->part_pay ?? null, $this);

        $this->faktury[] = $f;
    }
    function    set_fin_invoice_value( String $s)
    {
        $this->wywolane[__FUNCTION__] = true;

        $f = new faktura_w_db( "FINAL " . ( $this->type_of_job ?? ""), $this->tofloat( $s), $this->part_pay ?? null, $this);

        $this->faktury[] = $f;
    }
    function    set_invoice_value(String $s)
    {
        $this->wywolane[__FUNCTION__] = true;
        if ( ( $this->wywolane['set_fin_invoice_value'] ?? false) || ( $this->wywolane['set_ini_invoice_value'] ?? false))
            return;

        $f = new faktura_w_db( $this->rz . " " . ( $this->type_of_job ?? ""), $this->tofloat( $s), $this->part_pay ?? null, $this);
        // $f = new faktura_w_db( $this->nu_claim, $this->tofloat( $s), $this->part_pay ?? null, $this);
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
    function    set_lop_or_aob( string $s)
    {
        if ( $s == 'LOP' || $s == 'HO' || $s == 'AOB' || $s == 'Estimates' || $s == 'PA' || $s == 'DTP' || $s == 'Flood')
        {
            $this->wywolane[__FUNCTION__] = true;
            $this->type_of_claim = $s;
        }
    }
}
class ImportClaims
{
    public array $xlClaims;
    static public insuredsDb $dBinsL;
    static public insurance_coDb $dBincoL;
    static public law_firmDb $dBlawFL;
    static public attorneysDb $dBattL;
    public claimsDb $dBclsL;
    public fakturaDb $dBinvl;
    public static int $provider;
    public static int $portfolio;
    static public array $naglowki;
    static public array $pusteNaglowki;
    public \PhpOffice\PhpSpreadsheet\Spreadsheet $spreadsheet;

    public function __construct( int $pv, int $pf)
    {
        self::$provider = $pv;
        self::$portfolio = $pf;
        new claimsAttachements();
    }
    public function zaladuj_db()
    {
        self::$dBinsL = new insuredsDb();
        self::$dBinsL->zaladuj_ins();
        self::$dBincoL = new insurance_coDb();
        self::$dBincoL->zaladuj();
        self::$dBlawFL = new law_firmDb();
        self::$dBlawFL->zaladuj();
        self::$dBattL = new attorneysDb();
        self::$dBattL->zaladuj();
        $this->dBclsL = new claimsDb();
        $this->dBclsL->zaladuj();
        $this->dBinvl = new fakturaDb();
        $this->dBinvl->zaladuj( $this->dBclsL);
    }
    public static function import_claims_from_excel( Vtiger_Record_Model $recordModel)
    {
        \App\Log::warning( 'ImportClaims::import_claims_from_excel F-' . memory_get_usage( false) . " T-" . memory_get_usage( true));;

        $path = $recordModel->getFileDetails()['path'];
        $fn = $recordModel->get('filename');
        $at = $recordModel->getFileDetails()['attachmentsid'];

        $pf = Vtiger_Record_Model::getInstanceById( \App\Request::_get('portfolio'));
        if ( empty( $pf))
        {
            $recordModel->set( 'verification_warnings', "Nothing imported - the workflow must be called from Portfolios module");
            $recordModel->save();
            return;
        }
        $if = $pf->get( 'imported_claims_spreadsheets');
        if ( !empty( $if))
        {
            \App\Log::warning( 'ImportClaims::already_started : ' . $fn);
            $recordModel->set( 'verification_warnings', 'ImportClaims::already_started : ' . $fn . PHP_EOL .
                'clear Portfolio->Other->Imported Claims Spreadsheet in case of error');
            $recordModel->save();
            return;
        }
        $pf->set( 'imported_claims_spreadsheets', 'Started at ' . date( 'h:i:s A'));
        $pf->save();

        \App\Log::warning( 'ImportClaims::portfolioId :' . \App\Request::_get('portfolio') . " path: " . $path . " fn: " . $fn . " at: " . $at);

       $m = new ImportClaims( \App\Request::_get('provider'), \App\Request::_get('portfolio'));
       try
       {
           $recordModel->set( 'verification_warnings', 'Import started');
           $recordModel->save();
           $m->zaladuj_db();
           $m->czytaj( $path . $at);
           $stat = $fn . ' successfully read - processing claims';
           $recordModel->set( 'verification_warnings', $stat);
           \App\Toasts::addToast( \App\User::getCurrentUserId(), $stat);
           $recordModel->save();
           $m->utworz_nowe_claimy_w_pam();
           $m->utworz_nowe_claimy_w_db( $recordModel);
           $w = new nowy_Excel($m->xlClaims, ImportClaims::$naglowki, $m->spreadsheet);
           $status = $w->zapisz_Xlsx( $path . $at);
           $m->spreadsheet->disconnectWorksheets();
           $wfs = new \VTWorkflowManager();
           $workflow = $wfs->retrieveByName('CREATE_HO_ATTORNEY_CONFIRMATION_REQUESTS', 'Portfolios');
           try
           {
               if (isset($workflow))
                   $workflow->performTasks($pf);
           }
           catch ( Exception $ex)
           {
                $wfm = $ex->getMessage();
                throw $ex;
           }
       }
       catch ( AttorneyException $ex)
       {
           \App\Log::error($ex);
           $status = "HO Critical Error at row " . $ex->getCode() . PHP_EOL .
                $ex->getMessage();
       }
       catch ( Throwable $e)
       {
           \App\Log::error( $e);
           $status = "Serious error";
       }
       finally
       {
           $pf->set( 'imported_claims_spreadsheets', '');
           $pf->save();
       }

        \App\Log::warning( 'ImportClaims::status : ' . $status . PHP_EOL . $wfm);
        \VTWorkflowUtils::createNotificationRaw([\App\User::getCurrentUserId()], $fn . ' upload status', $status, 'PLL_USERS');
        \App\Toasts::addToast( \App\User::getCurrentUserId(), $status, 'infoSticky');
        $recordModel->set( 'filename', 'rep_' . $fn);
        $recordModel->set( 'verification_warnings', $status . PHP_EOL . $wfm);
        $recordModel->save();
        \App\Log::warning( 'ImportClaims::pamiec_po F-' . memory_get_usage( false) . " T-" . memory_get_usage( true));;
    }
    public function utworz_nowe_claimy_w_pam()
    {
        $claims = [];
        foreach ($this->xlClaims as $xc)
        {
            if ( $xc->status == claim_in_xls_row::BLAD_REK || $xc->status == claim_in_xls_row::TERMINATED)
                continue;

            $zlc = $claims[$xc->nu_claim] ?? $this->dBclsL->claims[$xc->nu_claim] ?? null;
            unset( $c);
            if ( $zlc != null)
            {
                if ( $xc->sprawdz_kolumny( $zlc[0]) == claim_in_xls_row::BLAD_REK)
                    continue;
                foreach ( $zlc as $zc)
                {
                    if ($xc->czy_duplikat( $zc))
                    {
                        $xc->status = claim_in_xls_row::POWTORZ;
                        goto next_xc;
                    }
                    if ( ( $zc->zXl ?? false) && $xc->czy_ten_sam_claim( $zc))
                    {
                        $c = true;
                        $zc->faktury = array_merge( $zc->faktury, $xc->faktury);
                        foreach ( explode( ',', $zc->type_of_job ) as $toj)
                            if ( $toj == ( $xc->type_of_job ?? ""))
                                goto next_xc;

                        $zc->type_of_job .= ',' . ( $xc->type_of_job ?? "");
                        goto next_xc;
                    }
                }
            }

            if ( !isset( $c))
            {
                $nc = new claim_in_db();
                $nc->init_zXl($xc);
                $claims[ $xc->nu_claim][] = $nc;
            }

            next_xc:
        }

        foreach ( array_keys( $claims) as $k)
        {
            $zl = $this->dBclsL->claims[$k] ?? null;
            if ( isset( $zl))
                $this->dBclsL->claims[$k] = array_merge( $zl, $claims[$k]);
            else
                $this->dBclsL->claims[$k] = $claims[$k];
        }
    }
    public function utworz_nowe_claimy_w_db( $recordModel)
    {
        $pt = 0;
        $clnr = 0;
        $clrqty = 0;
        foreach ($this->dBclsL->claims as $cli)
            foreach ($cli as $dc)
                if (isset($dc->zXl) && $dc->faktury[0]->xcl->status == 0)
                    $clrqty++;
        foreach ($this->dBclsL->claims as $cli)
            foreach ($cli as $dc)
                if ( isset( $dc->zXl))
                    try
                    {
                        $nbr = $dc->zapisz_do_db();
                        $vw = $recordModel->get( 'verification_warnings');
                        $stat = "Importing \"" . $recordModel->get('filename') . '"...' . PHP_EOL . ++$clnr . "/" . $clrqty . " claims imported so far. Please wait... " . PHP_EOL;
                        \App\Log::warning( 'ImportClaims::pamiec w trakcie F-' . memory_get_usage( false) . " T-" . memory_get_usage( true));
                        $recordModel->set( 'verification_warnings', $stat . $vw);
                        if ( ( time() - $pt) > 30)
                        {
                            \App\Toasts::addToast( \App\User::getCurrentUserId(), $stat);
                            $pt = time();
                        }
                        $recordModel->save();
                    }
                    catch( Throwable $ex)
                    {
                        for( $i = 0; $i < sizeof( $dc->faktury); $i++)
                        {
                            $dc->faktury[$i]->xcl->status = claim_in_xls_row::BLAD_REK;
                            stat_append($dc->faktury[$i]->xcl->status_row, quoted_printable_encode( $ex->getMessage()));
                        }
                    }
    }
    public function czytaj( string $plikWej)
    {
        $an = new analizator_claim();
        $reader = new Xlsx();
        // $reader->setReadDataOnly( true);
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
        self::$pusteNaglowki = [];
        self::$naglowki['insured_name'][1] = 'Insured Name';
        self::$naglowki['insured_name'][0] = NumberFormat::FORMAT_TEXT;
        $pr = 0;
        foreach ( $ws->getRowIterator() as $rz )
        {
            $koli = 0;
            $c = new claim_in_xls_row( $rzi);
            if ($rzi++ == 1)
            {
                foreach ($rz->getCellIterator() as $kom)
                {
                    $k = preg_replace("/\s+/u", "", $kom->getValue());
                    $k = str_replace( '-','', $k);
                    $k = trim( $k);
                    $an->analizuj( $koli++, $k, self::$naglowki, self::$pusteNaglowki, $kom->getValue());
                }
                continue;
            }
            else
            {
                $pr++;
                if ( $pr == 51)
                {
                    $c->status = claim_in_xls_row::TERMINATED;
                    $c->status_row = "Import terminated, too many empty rows";
                    $this->xlClaims = array_splice( $this->xlClaims, 0, $rzi-53);
                    $c->rz -= 50;
                    $this->xlClaims[] = $c;
                    break;
                }
                foreach ($rz->getCellIterator() as $kom)
                {
                    $k = preg_replace("/[\s\x{200C}\x{200B}]+/u", " ", $kom->getValue());

                    $k = trim($k);
                    if ( !empty( $k))
                        $pr = 0;
                    $an->ustaw( $c, $koli++, $k);
                }
            }
            $c->sprawdz_obowiazkowe();
            $this->xlClaims[] = $c;
        }
    }
}
class insured_in_db
{
    public string $name;
    public ?string $first_name_1;
    public ?string $last_name_1;
    public ?string $first_name_2;
    public ?string $last_name_2;
    public ?string $first_name_3;
    public ?string $last_name_3;
    public ?string $first_name_4;
    public ?string $last_name_4;
    public ?string $e_mail;
    public ?string $street;
    public ?string $city;
    public ?string $state;
    public ?string $zip;
    public ?string $id;
    public ?string $phone;
    public bool   $built;

    public function ins_ustaw_z_db( array $par)
    {
        $this->name = claim_in_xls_row::normalizuj_name( $par['insured_name']);
        $this->first_name_1 = $par['insured1_first_name']; $this->last_name_1 = $par['insured1_last_name'];
        $this->first_name_2 = $par['insured2_first_name']; $this->last_name_2 = $par['insured2_last_name'];
        $this->first_name_3 = $par['insured3_first_name']; $this->last_name_3 = $par['insured3_last_name'];
        $this->first_name_4 = $par['insured4_first_name']; $this->last_name_4 = $par['insured4_last_name'];
        $this->street = $par['street'];
        $this->zip = $par['zip'];
        $this->city = $par['city'];
        $this->phone = $par['phone'];
        $this->state = $par['state'];
        $this->e_mail = strtolower( $par['e_mail']);
        $this->id = $par['id'];
    }
    public function ustaw_z_xl( $cl)
    {
        $this->name = $cl->insured_name ?? null;
        $this->first_name_1 = $cl->first_name_1 ?? null;
        $this->last_name_1 = $cl->last_name_1 ?? null;
        $this->first_name_2 = $cl->first_name_2 ?? null;
        $this->last_name_2 = $cl->last_name_2 ?? null;
        $this->first_name_3 = $cl->first_name_3 ?? null;
        $this->last_name_3 = $cl->last_name_3 ?? null;
        $this->first_name_4 = $cl->first_name_4 ?? null;
        $this->last_name_4 = $cl->last_name_4 ?? null;
        $this->e_mail = $cl->ins_e_mail ?? null;
        $this->street = $cl->ins_street ?? null;
        $this->city = $cl->ins_city ?? null;
        $this->state = $cl->ins_state ?? null;
        $this->zip = $cl->ins_zip ?? null;
        $this->phone = $cl->ins_phone ?? null;

        if (empty($this->name))
        {
            $this->name = $this->first_name_1 . " " . $this->last_name_1;
            $this->built = true;
        }
    }
    public function get_key() : string
    {
        $k = $this->name . $this->street;
        $k = strtolower( $k);
        $k = preg_replace( '/\s+/', '', $k);
        return str_replace( '-', '', $k);
    }
}
class insuredsDb
{
    public array $insureds;
    public array  $states;

    function zaladuj_states()
    {
        $rez = \App\Fields\Picklist::getValues('state');
        foreach ($rez as $par)
            $this->states[] = $par['state'];
    }
    function zaladuj_ins()
    {
        $this->zaladuj_states();

        $qg = new \App\QueryGenerator( 'Insureds');
        $q = $qg->setField( ['id','insured_name',
            'insured1_first_name', 'insured1_last_name',
            'insured2_first_name', 'insured2_last_name',
            'insured3_first_name', 'insured3_last_name',
            'insured4_first_name', 'insured4_last_name',
            'street', 'zip', 'city', 'state', 'phone',
            'e_mail'])->createQuery();
        $rez = $q->all();

        foreach ( $rez as $par)
        {
            $i = new insured_in_db();
            $i->ins_ustaw_z_db( $par);

            $this->insureds[ $i->get_key()] = $i;
        }
    }
    function dopasuj_state( string $s1) : string
    {
        foreach ( $this->states as $s)
            if ( str_starts_with( $s, $s1))
                return $s;
        foreach ( $this->states as $s)
            if ( strstr( $s, $s1))
                return $s;

        return "";
    }
    function sprawdz_lub_stworz( $cl)
    {
        $i = $this->insureds[ $cl->insured->get_key()] ?? null;
        if ( isset( $i))
        {
            $cl->insured->id = $i->id;
            unset( $insured);
            if ( empty( $i->first_name_1) && !empty( $cl->insured->first_name_1))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'insured1_first_name', $cl->insured->first_name_1);
            }
            if ( empty( $i->last_name_1) && !empty( $cl->insured->last_name_1))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'insured1_last_name', $cl->insured->last_name_1);
            }
            if ( empty( $i->first_name_2) && !empty( $cl->insured->first_name_2))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'insured2_first_name', $cl->insured->first_name_2);
            }
            if ( empty( $i->last_name_2) && !empty( $cl->insured->last_name_2))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'insured2_last_name', $cl->insured->last_name_2);
            }
            if ( empty( $i->first_name_3) && !empty( $cl->insured->first_name_3))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'insured3_first_name', $cl->insured->first_name_3);
            }
            if ( empty( $i->last_name_3) && !empty( $cl->insured->last_name_3))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'insured3_last_name', $cl->insured->last_name_3);
            }
            if ( empty( $i->first_name_4) && !empty( $cl->insured->first_name_4))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'insured4_first_name', $cl->insured->first_name_4);
            }
            if ( empty( $i->last_name_4) && !empty( $cl->insured->last_name_4))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'insured4_last_name', $cl->insured->last_name_4);
            }
            if ( empty( $i->e_mail) && !empty( $cl->insured->e_mail))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'e_mail', $cl->insured->e_mail);
            }
            if ( empty( $i->state) && !empty( $cl->insured->state))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'state', $this->dopasuj_state( $cl->insured->state));
            }
            if ( empty( $i->zip) && !empty( $cl->insured->zip))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'zip', $cl->insured->zip);
            }
            if ( empty( $i->phone) && !empty( $cl->insured->phone))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $cl->insured->phone = str_replace( ['(',')'], '', $cl->insured->phone);
                $cl->insured->phone = str_replace( ' ', '-', $cl->insured->phone);
                if ( !str_starts_with( $cl->insured->phone, '+'))
                    $cl->insured->phone = "+1 " . $cl->insured->phone;

                $insured->set( 'phone', $cl->insured->phone);
            }
            if ( empty( $i->city) && !empty( $cl->insured->city))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'city', $cl->insured->city);
            }
            if ( empty( $i->street) && !empty( $cl->insured->street))
            {
                $insured = $insured ?? Vtiger_Record_Model::getInstanceById( $i->id, 'Insureds');
                $insured->set( 'street', $cl->insured->street);
            }
            if ( isset( $insured))
            {
                $insured->save();
                $cl->faktury[0]->xcl->kolumny['insured_name'][1] = claim_in_xls_row::BLAD_KOL;
                $cl->faktury[0]->xcl->kolumny['first_name_1'][1] = claim_in_xls_row::BLAD_KOL;
                stat_append( $cl->faktury[0]->xcl->status_kol, "Insured attr. set");
            }

            return null;
        }

        $insured = Vtiger_Record_Model::getCleanInstance('Insureds');
        $insured->set('insured_name', $cl->insured->name);
        if ( isset( $cl->insured->first_name_1))
            $insured->set('insured1_first_name', $cl->insured->first_name_1);
        if ( isset( $cl->insured->last_name_1))
            $insured->set('insured1_last_name', $cl->insured->last_name_1);
        if ( isset( $cl->insured->first_name_2))
            $insured->set('insured2_first_name', $cl->insured->first_name_2);
        if ( isset( $cl->insured->last_name_2))
            $insured->set('insured2_last_name', $cl->insured->last_name_2);
        if ( isset( $cl->insured->first_name_3))
            $insured->set('insured3_first_name', $cl->insured->first_name_3);
        if ( isset( $cl->insured->last_name_3))
            $insured->set('insured3_last_name', $cl->insured->last_name_3);
        if ( isset( $cl->insured->first_name_4))
            $insured->set('insured4_first_name', $cl->insured->first_name_4);
        if ( isset( $cl->insured->last_name_4))
            $insured->set('insured4_last_name', $cl->insured->last_name_4);
        if ( isset( $cl->insured->e_mail))
            $insured->set('e_mail', $cl->insured->e_mail);
        if ( isset( $cl->insured->street))
            $insured->set('street', $cl->insured->street);
        if ( isset( $cl->insured->zip))
            $insured->set('zip', $cl->insured->zip);
        if ( isset( $cl->insured->state))
            $insured->set('state', $this->dopasuj_state( $cl->insured->state));
        if ( isset( $cl->insured->phone))
        {
            $cl->insured->phone = str_replace( ['(',')'], '', $cl->insured->phone);
            $cl->insured->phone = str_replace( ' ', '-', $cl->insured->phone);
            if ( !str_starts_with( $cl->insured->phone, '+'))
                $cl->insured->phone = "+1 " . $cl->insured->phone;
            $insured->set('phone', $cl->insured->phone);
        }
        if ( isset( $cl->insured->city))
            $insured->set('city', $cl->insured->city);

        $insured->save();
        $cl->insured->id = $insured->get( 'id');
        $this->insureds[ $cl->insured->get_key()]  = $cl->insured;
        $cl->faktury[0]->xcl->kolumny['insured_name'][1] = claim_in_xls_row::BLAD_KOL;
        $cl->faktury[0]->xcl->kolumny['first_name_1'][1] = claim_in_xls_row::BLAD_KOL;
        stat_append( $cl->faktury[0]->xcl->status_kol, "New insured created");
    }
}
class law_firmDb
{
    public array $firms;
    public array $firmsIds;
    public static law_firmDb $instance_;

    function zaladuj()
    {
        $qg = new \App\QueryGenerator( 'LawFirms');
        $qg->setField( [ 'id', 'law_firm_name']);
        $q = $qg->createQuery();

        $rez = $q->all();
        $this->firms = [];
        foreach ( $rez as $pos)
        {
            $lf = self::normalizujNa( $pos['law_firm_name']);
            $this->firms[$lf] = $pos['id'];
            $this->firmsIds[$pos['id']] = $lf;
        }

        $qg = new \App\QueryGenerator( 'LawFirmAliases');
        $qg->setField( [ 'law_firm_alias', 'law_firm']);
        $q = $qg->createQuery();

        $rez = $q->all();
        foreach ( $rez as $pos)
            $this->firms[self::normalizujNa( $pos['law_firm_alias'])] = $pos['law_firm'];

        law_firmDb::$instance_ = $this;
    }
    function normalizujNa( $at) : string
    {
        $at = preg_replace("/[[:punct:]]/", "", $at);
        $at = strtoupper($at);

        if (str_ends_with($at, " PL"))
            $at = substr($at, 0, -3);
        if (str_ends_with($at, " PA"))
            $at = substr($at, 0, -3);
        if (str_ends_with($at, " PC"))
            $at = substr($at, 0, -3);
        if (str_ends_with($at, " PLC"))
            $at = substr($at, 0, -4);
        if (str_ends_with($at, " LLP"))
            $at = substr($at, 0, -4);
        if (str_ends_with($at, " LTD"))
            $at = substr($at, 0, -4);
        if (str_ends_with($at, " INC"))
            $at = substr($at, 0, -4);
        if (str_ends_with($at, " LPA"))
            $at = substr($at, 0, -4);
        if (str_ends_with($at, " LLC"))
            $at = substr($at, 0, -4);
        if (str_ends_with($at, " PLLC"))
            $at = substr($at, 0, -5);
        if (str_ends_with($at, " PLLP"))
            $at = substr($at, 0, -5);

        return preg_replace("/\s+/u", "", strtolower( $at)) ?? "";
    }
    function normalizuj( $at) : array
    {
        $na = self::normalizujNa( $at);
        $fi = $this->firms[$na];
        $na = $this->firmsIds[$fi] ?? "";
        return [ $na, $fi];
    }
    function sprawdz( $cl)
    {
        $lf = self::normalizuj( $cl->ho_law_firm);

        if ( !empty( $cl->ho_law_firm))
            if ( empty($lf[1]))
                for( $id = 0; $id < sizeof( $cl->faktury); $id++)
                {
                    $cl->faktury[$id]->xcl->kolumny['ho_law_firm'][1] = claim_in_xls_row::BLAD_KOL;
                    if ( empty( $cl->ho_law_firm_id))
                    {
                        $cl->faktury[$id]->xcl->status = claim_in_xls_row::BLAD_REK;
                        stat_append($cl->faktury[$id]->xcl->status_row, "Law firm not known ");
                    }
                    else
                        stat_append($cl->faktury[$id]->xcl->status_kol, "Law firm not known ");
                }
            else
            {
                if (!empty($cl->ho_law_firm_id) && $lf[0] != $this->firmsIds[$cl->ho_law_firm_id])
                    for ($id = 0; $id < sizeof($cl->faktury); $id++)
                    {
                        $cl->ho_law_firm_id = $lf[1];
                        $cl->faktury[$id]->xcl->kolumny['ho_law_firm'][1] = claim_in_xls_row::BLAD_KOL;
                        stat_append($cl->faktury[$id]->xcl->status_kol, "Attorney law firm different & overwritten");
                    }
                elseif ( empty( $cl->ho_law_firm_id))
                    $cl->ho_law_firm_id = $lf[1];
            }
    }
}
class attorneysDb
{
    public array $attorneys;
    public static attorneysDb $instance_;

    function zaladuj()
    {
        $qg = new \App\QueryGenerator( 'Attorneys');
        $qg->setField( [ 'id', 'attorney_name', 'law_firm', 'email']);
        $qg->addRelatedField([ 'sourceField' => 'law_firm', 'relatedModule' => 'LawFirms', 'relatedField' => 'law_firm_name' ]);
        $q = $qg->createQuery();

        $rez = $q->all();
        $this->attorneys = [];
        foreach ( $rez as $pos)
        {
            $at = self::normalizuj( $pos['attorney_name']);
            $lf = law_firmDb::$instance_->normalizuj($pos['law_firmLawFirmslaw_firm_name'])[0];
            $this->attorneys[$at][] = [$pos['id'], $lf, $pos['law_firm'], $pos['email']];
        }
        attorneysDb::$instance_ = $this;
    }
    static function normalizuj( $at) : string
    {
        $at = preg_replace("/\s+/u", "", $at);
        $at = preg_replace("/[[:punct:]]/", "", $at);
        $at = strtolower( $at);

        if ( str_ends_with( $at, "esq"))
            $at = substr( $at, 0, -3);
        if ( str_ends_with( $at, "esquire"))
            $at = substr( $at, 0, -7);

        return $at;
    }
    function sprawdzPre( $cl)
    {
        $at = self::normalizuj( $cl->pre_attorney);
        $i = $this->attorneys[$at] ?? null;
        if ( !empty( $cl->pre_attorney) && empty( $i) && empty( $cl->ho_attorney))
            for( $i = 0; $i < sizeof( $cl->faktury); $i++)
            {
                $cl->faktury[$i]->xcl->kolumny['pre_attorney'][1] = claim_in_xls_row::BLAD_KOL;
                stat_append($cl->faktury[$i]->xcl->status_kol, "Pre-Attorney name not known ");
            }
    }

    function sprawdz_zXl( claim_in_xls_row $xl)
    {
        if ( empty( $xl->ho_attorney))
            throw new AttorneyException( "Empty HO Attorney", $xl->rz);
        if ( empty( $xl->ho_law_firm))
            throw new AttorneyException( "Empty HO Law Firm", $xl->rz);

        $at = self::normalizuj( $xl->ho_attorney);
        $lf = law_firmDb::$instance_->normalizuj( $xl->ho_law_firm)[0];

        $i = $this->attorneys[$at];

        if ( !isset( $i))
            throw new AttorneyException( "Unkown Attorney " . $xl->ho_attorney, $xl->rz);

        foreach ( $i as $at)
        {
            if ( $lf != $at[1])
                continue;
            if ( empty( $at[3]))
                throw new AttorneyException( "Empty Attorney's e-mail", $xl->rz);
            return;
        }

        throw new AttorneyException( "HO Law Firm " . $xl->ho_law_firm . " doesn't match to HO Attorney ", $xl->rz);
    }
    function sprawdz( $cl)
    {
        $v = preg_replace( '/\s+/u' , '' , strtolower( $cl->ho_attorney));

        if ( str_starts_with( $v, "pleaseprovide"))
        {
            for( $i = 0; $i < sizeof( $cl->faktury); $i++)
            {
                $cl->faktury[$i]->xcl->status = claim_in_xls_row::BLAD_REK;
                stat_append($cl->faktury[$i]->xcl->status_row, "HO Attorney name not known ");
            }
            return;
        }

        $this->sprawdzPre( $cl);

        $at = self::normalizuj( $cl->ho_attorney);
        $lf = law_firmDb::$instance_->normalizuj( $cl->ho_law_firm)[0];

        $i = $this->attorneys[$at] ?? null;

        if ( isset( $i) && sizeof( $i) > 1)
            $dokladne = true;

        foreach ( $i ?? [] as $at)
        {
            if ( isset( $dokladne))
                if ( $lf != $at[1])
                    continue;

            $cl->ho_attorney_id = $at[0];
            $cl->ho_law_firm_id = $at[2];
        }

        if ( empty( $cl->ho_attorney_id) && !empty( $cl->ho_attorney))
            for( $i = 0; $i < sizeof( $cl->faktury); $i++)
            {
                $cl->faktury[$i]->xcl->kolumny['ho_attorney'][1] = claim_in_xls_row::BLAD_KOL;
                stat_append($cl->faktury[$i]->xcl->status_kol, "HO Attorney name not known ");
            }
    }
    function set_dtp_attorney( $faktury, $cldb, ?string $at)
    {
        $f = $this->attorneys[attorneysDb::normalizuj( $at)] ?? null;
        switch ( sizeof( $f ?? []))
        {
            case    0   :
                            return;
            case    1   :
                            $cldb->set('aob_dtp_attorney', $f[0][0]);
                            break;
            default     :
                foreach ( $faktury as $fak)
                {
                    $fak->xcl->kolumny['ho_attorney'][1] = claim_in_xls_row::BLAD_KOL;
                    stat_append($fak->xcl->status_kol, "Two AOB/DTP attorneys with the same name");
                }
        }
    }
}
class insurance_coDb
{
    public array $companies;

    function zaladuj()
    {
        $qg = new \App\QueryGenerator( 'InsuranceCompanies');
        $qg->setField( [ 'id', 'insurance_company_name']);
        $q = $qg->createQuery();

        $rez = $q->all();
        $this->companies = [];
        foreach ( $rez as $pos)
            $this->companies[strtolower( $pos['insurance_company_name'])] = $pos['id'];

        $qg = new \App\QueryGenerator( 'InsuranceCompanyAliases');
        $qg->setField( [ 'insurance_company_alias', 'insurance_company']);
        $q = $qg->createQuery();

        $rez = $q->all();
        foreach ( $rez as $pos)
            $this->companies[strtolower( $pos['insurance_company_alias'])] = $pos['insurance_company'];
    }
    function sprawdz( $cl)
    {
        $i = $this->companies[strtolower( $cl->insurance_company)] ?? null;
        if ( !isset($i))
        {
            $cl->faktury[0]->xcl->kolumny['insurance_company'][1] = claim_in_xls_row::BLAD_KOL;
            stat_append( $cl->faktury[0]->xcl->status_kol, "Insurance company not known ");
            return;
        }

        $cl->insurance_company_id = $i;
    }
}
class faktura_w_db
{
    public string  $nazwa;
    public string  $type_of_job;
    public float  $wartosc;
    public ?float  $estimate_am;
    public ?float  $prior_coll;
    public  $xcl;
    static int $fl = 0;

    public function __construct( string $n, float $w, ?float $p, claim_in_xls_row &$c = null)
    {
        $this->nazwa = trim( $n);
        $this->wartosc = $w;
        $this->prior_coll = $p;
        $this->type_of_job = $c->type_of_job ?? "";
        $this->xcl = $c;
    }
    public function zapisz_w_db( claim_in_db $cl)
    {
        $cldb = Vtiger_Record_Model::getCleanInstance('ClaimedInvoices');
        $cldb->set('claimed_invoice_name', $this->nazwa);
        $cldb->set('invoice_value', $this->wartosc);
        $cldb->set('prior_collections', $this->prior_coll);
        $cldb->set('claim', $cl->claim_id);
        $cldb->set('invoice_date', $cl->date_of_service);
        $cldb->set('type_of_job', $cl->type_of_job);
        if ( isset( $this->estimate_am))
            $cldb->set('estimate_amount', $this->estimate_am);
        $cldb->save();
    }
}
class fakturaDb
{
    function zaladuj( claimsDb $cd)
    {
        $qg = new \App\QueryGenerator( 'ClaimedInvoices');
        $qg->setField( [ 'claim', 'invoice_value', 'claimed_invoice_name', 'prior_collections']);
        $qg->addRelatedField([ 'sourceField' => 'claim', 'relatedModule' => 'Claims', 'relatedField' => 'claim_number' ]);
        $q = $qg->createQuery();

        $rez = $q->all();
        foreach ( $rez as $pos)
        {
            $cl = $cd->claims[$pos['claimClaimsclaim_number']] ?? null;
            if ( isset( $cl))
                foreach ( $cl as $c)
                    if ( $c->claim_id == $pos['claim'])
                    {
                        if ( isset( $pos['claimed_invoice_name']) && isset( $pos['invoice_value']))
                            $c->faktury[] = new faktura_w_db( $pos['claimed_invoice_name'], $pos['invoice_value'], $pos['prior_collections']);
                    }
        }
    }
}
class claim_in_db
{
    public bool $zXl;
    public ?string $policy_number;
    public ?string $type_of_job;
    public ?string $type_of_claim;
    public ?string $date_of_service;
    public ?string $date_of_fn;
    public ?string $date_of_loss;
    public ?string $insurance_company;
    public ?string $ho_law_firm;
    public ?string $ho_attorney;
    public ?float $prior_coll;
    public ?string $plst;
    public string $claim_number;
    public ?string $pre_ccn;
    public ?string $pre_county;
    public ?string $pre_attorney;
    public int $insurance_company_id;
    public int $ho_law_firm_id;
    public int $ho_attorney_id;
    public array $faktury;
    public insured_in_db $insured;

    public function cl_ustaw_z_db( array $pos)
    {
        $this->claim_id = $pos['id'];
        $this->insurance_company = $pos['insurance_companyInsuranceCompaniesinsurance_company_name'];
        $this->ho_law_firm = $pos['ho_law_firmLawFirmslaw_firm_name'];
        $this->ho_attorney = $pos['ho_attorneyAttorneysattorney_name'];
        $this->pre_attorney = $pos['pre_attorney_name'];
        $this->policy_number = $pos['policy_number'];
        $this->claim_number = $pos['claim_number'];
        $this->type_of_job = $pos['type_of_job'];
        $this->type_of_claim = $pos['type_of_claim'];
        $this->date_of_loss = $pos['date_of_loss'];
        $this->date_of_service = $pos['date_of_service'];
        $this->insured = new insured_in_db();
        $this->insured->e_mail = strtolower( $pos['insuredInsuredse_mail']);
        $this->insured->name = claim_in_xls_row::normalizuj_name( strtolower( $pos['insuredInsuredsinsured_name']));
        $this->insured->street = $pos['insuredInsuredsstreet'];
    }
    public function init_zXl( claim_in_xls_row &$xc)
    {
        $this->zXl = true;

        $this->insured = new insured_in_db();
        $this->insured->ustaw_z_xl( $xc);
        $this->insurance_company = $xc->insurance_company ?? null;
        $this->ho_law_firm = $xc->ho_law_firm ?? null;
        $this->ho_attorney = $xc->ho_attorney ?? null;
        $this->policy_number = $xc->nu_policy ?? null;
        $this->type_of_job = $xc->type_of_job ?? null;
        $this->type_of_claim = $xc->type_of_claim ?? null;
        $this->date_of_loss = $xc->dol ?? null;
        $this->claim_number = $xc->nu_claim ?? null;
        $this->date_of_service = $xc->dos ?? null;
        $this->date_of_fn = $xc->dofn ?? null;
        $this->pre_county = $xc->county_case ?? null;
        $this->pre_attorney = $xc->pre_attorney ?? null;
        $this->pre_ccn = $xc->court_case ?? null;
        $this->plst = $xc->plst ?? null;
        $this->faktury = $xc->faktury;
    }
    function zapisz_do_db() : ?string
    {
        ImportClaims::$dBinsL->sprawdz_lub_stworz($this);
        ImportClaims::$dBincoL->sprawdz($this);
        ImportClaims::$dBattL->sprawdz($this);
        ImportClaims::$dBlawFL->sprawdz($this);

        if ($this->faktury[0]->xcl->status == claim_in_xls_row::BLAD_REK)
            return "ERROR";

        $cldb = Vtiger_Record_Model::getCleanInstance('Claims');
        $cldb->set('policy_number', $this->policy_number);
        $adj = 0;
        foreach ($this->faktury as $f)
            $adj += $f->wartosc;
        $cldb->set('total_bill_amount', $adj);
        $cldb->set('claim_number', $this->claim_number);
        $cldb->set('type_of_job', $this->type_of_job);
        $cldb->set('type_of_claim', $this->type_of_claim);
        $cldb->set('date_of_loss', $this->date_of_loss);
        $cldb->set('date_of_service', $this->date_of_service);
        $cldb->set('date_of_first_notification', $this->date_of_fn);
        $cldb->set('pre_court_case_number', $this->pre_ccn);
        $cldb->set('pre_county', $this->pre_county);
        $cldb->set('pre_attorney_name', $this->pre_attorney ?? $this->ho_attorney);
        ImportClaims::$dBattL->set_dtp_attorney( $this->faktury, $cldb, $this->pre_attorney);
        $cldb->set('insured', $this->insured->id);
        $cldb->set('provider', ImportClaims::$provider);
        $cldb->set('portfolio', ImportClaims::$portfolio);
        $at = attorneysDb::normalizuj($this->pre_attorney);
        if (!empty($at))
        {
            if ($at == "robertgonzalez" || $at == "flinslaw" || $at == "flins" || $at == "floridainsurancelawgroup" || $at == "robertfgonzalez")
                $cldb->set('conducted_by', 'FLINSLAW');
            else
                $cldb->set('conducted_by', 'Outside');
        }
        else
        {
            $at = attorneysDb::normalizuj($this->ho_attorney);
            $lf = law_firmDb::$instance_->normalizujNa( $this->ho_law_firm);
            if (($at == "robertgonzalez") && (($lf == 'flinslaw') || ( $lf == "floridainsurancelawgroup") || empty( $lf)))
                $cldb->set('conducted_by', 'FLINSLAW');
            else
                $cldb->set('conducted_by', 'Outside');
        }

        if ( isset( $this->insurance_company_id))
            $cldb->set('insurance_company', $this->insurance_company_id);
        if ( isset( $this->ho_law_firm_id))
            $cldb->set('ho_law_firm', $this->ho_law_firm_id);
        if ( isset( $this->ho_attorney_id))
            $cldb->set('ho_attorney', $this->ho_attorney_id);
        $cldb->set('pre_litigation_status', $this->plst);

        $cldb->save();

        $this->claim_id = $cldb->get('id');

        foreach ( $this->faktury as $f)
        {
            $f->zapisz_w_db( $this);
            claimsAttachements::$instance_->zapiszWierszAtt( $this->claim_id, $f->xcl);
        }

        return $cldb->get('number');
    }
}
class claimsDb
{
    public array $claims;

    function zaladuj()
    {
        $qg = new \App\QueryGenerator( 'Claims');
        $qg->setField( ['id', 'policy_number', 'claim_number', 'type_of_job', 'type_of_claim', 'date_of_loss', 'date_of_service', 'insured', 'pre_attorney_name']);
        // $qg->setCustomColumn( [ 'u_yf_claimedinvoices.value', 'u_yf_claimedinvoices.claimed_invoice_name'] );

        $qg->addRelatedField([ 'sourceField' => 'insurance_company', 'relatedModule' => 'InsuranceCompanies', 'relatedField' => 'insurance_company_name' ]);
        $qg->addRelatedField([ 'sourceField' => 'ho_law_firm', 'relatedModule' => 'LawFirms', 'relatedField' => 'law_firm_name' ]);
        $qg->addRelatedField([ 'sourceField' => 'ho_attorney', 'relatedModule' => 'Attorneys', 'relatedField' => 'attorney_name' ]);
        $qg->addRelatedField([ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'e_mail', ]);
        $qg->addRelatedField([ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'insured_name', ]);
        $qg->addRelatedField([ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'street', ]);
        $qg->addCondition( 'provider', ImportClaims::$provider, 'eid');
        $qg->addCondition( 'portfolio', ImportClaims::$portfolio, 'eid');
        // $q = $qg->addJoin(['LEFT JOIN', 'u_yf_claimedinvoices', 'u_yf_claimedinvoices.claim = u_yf_claims.claimsid']);
        $q = $qg->createQuery();

        $rez = $q->all();
        $this->claims = [];
        foreach ( $rez as $pos)
        {
            $cl = new claim_in_db();
            $cl->cl_ustaw_z_db( $pos);

            $cn = $pos['claim_number'];
            if ( empty( $cl->policy_number))
                continue;
            $this->claims[$cn][] = $cl;
        }
    }
}
class nowy_Excel
{
    public array $rowki;
    public array $naglowki;
    public string $status;
    public \PhpOffice\PhpSpreadsheet\Spreadsheet $wej_spreadsheet;
    public \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $ws;

    public function __construct( array $r, array $nag, \PhpOffice\PhpSpreadsheet\Spreadsheet $sp)
    {
        $this->rowki = $r;
        $this->naglowki = $nag;
        $this->wej_spreadsheet = $sp;
    }

    function zapisz_Xlsx( string $wynik, string $kom = null) : string
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $this->ws = $spreadsheet->getActiveSheet();
        $this->ws->setTitle( "Import Status");
        $r="";
        if ( !empty( $kom))
            $this->ws->getCell('A1')->setValue($kom);
        else
        {
            $this->ws->insertNewRowBefore(1, count($this->rowki));

            $this->ustawNaglowki();
            $r = $this->ustawRowki();
        }

        $spreadsheet->addExternalSheet( clone $this->wej_spreadsheet->setActiveSheetIndex( 0));

        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save( $wynik);
        $spreadsheet->disconnectWorksheets();
        unset( $spreadsheet);

        return $r;
    }
    public function ustawNaglowki()
    {
        $alphabet = range('A', 'Z');
        foreach ( range( 'A', 'Z') as $r)
            $alphabet[] = 'A' . $r;

        $coord = 'A1:' . $alphabet[sizeof( $this->naglowki)] . '1';
        $coordP = $alphabet[sizeof( $this->naglowki) + 1] . '1:' . $alphabet[sizeof( $this->naglowki) + sizeof( ImportClaims::$pusteNaglowki)] . '1';

        $this->ws->getStyle( $coord)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB( 'FF00B0F0');
        $this->ws->getStyle( $coord)->getAlignment()->setHorizontal( 'center');
        $this->ws->getStyle( $coordP)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB( 'FFC0C0C0');
        $this->ws->getStyle( $coordP)->getAlignment()->setHorizontal( 'center');
        $k = 2;

        $al = 1;
        $this->ws->getCellByColumnAndRow( 1, 1)->setValue( "Row imp. status");
        $this->ws->getColumnDimension( 'A')->setAutoSize(false);
        foreach ( array_keys( $this->naglowki) as $n)
        {
            $this->ws->getCellByColumnAndRow($k++, 1)->setValue($this->naglowki[$n][1]);
            $this->ws->getColumnDimension($alphabet[$al++])->setAutoSize( true);
        }
        foreach ( ImportClaims::$pusteNaglowki as $pn)
        {
            $this->ws->getCellByColumnAndRow($k++, 1)->setValue( $pn);
            $this->ws->getColumnDimension($alphabet[$al++])->setAutoSize(true);
        }
    }
    public function ustawRowki(): string
    {
        $alphabet = range('A', 'Z');
        $cnt = count( $this->rowki);
        $rowki_ok = 0;
        $rowki_warn = 0;
        $rowki_blad = 0;
        $rowki_dup = 0;
        for( $i = 0; $i < $cnt; $i++)
        {
            $cls = $this->rowki[$i];

            if ( $cls->status == claim_in_xls_row::BLAD_REK || $cls->status == claim_in_xls_row::TERMINATED)
            {
                    $this->ws->getStyle('A' . $cls->rz)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_RED);
                    $this->ws->getCell('A' . $cls->rz)->setValue( $cls->status_row);
            }

            $k = 2;
            $blad_kol = false;
            foreach ( array_keys( $this->naglowki) as $n)
            {
                $kol = $cls->kolumny[$n] ?? null;
                if (($this->naglowki[$n][0] ?? null) == NumberFormat::FORMAT_DATE_XLSX14)
                {
                    $dr = \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($kol[0]);
                    if ($dr == false)
                    {
                        stat_append($cls->status_kol, "Bad " . $this->naglowki[$n][1]);
                        $kol[1] = claim_in_xls_row::BLAD_KOL;
                    }
                    else
                        $kol[0] = $dr;
                }
                $this->ws->getCellByColumnAndRow($k, $cls->rz)->getStyle()->getNumberFormat()->setFormatCode($this->naglowki[$n][0] ?? "@");
                $this->ws->getCellByColumnAndRow($k, $cls->rz)->setValue($kol ? ($kol[0] ?? null) : null);
                if ($kol ? ($kol[1] ?? null) : null == claim_in_xls_row::BLAD_KOL)
                {
                    $blad_kol = true;
                    $this->ws->getStyle($alphabet[$k - 1] . $cls->rz)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_YELLOW);
                }
                $k++;
            }
            if (empty($cls->status_row) && !empty($cls->status_kol))
            {
                $this->ws->getCell('A' . $cls->rz)->setValue($cls->status_kol);
                $blad_kol = true;
            }
            switch ( $cls->status)
            {
                case    claim_in_xls_row::OK :
                {
                    if ($blad_kol == true)
                        $rowki_warn++;
                    else
                        $rowki_ok++;
                    break;
                }
                case    claim_in_xls_row::BLAD_REK :
                    $rowki_blad++; break;
                case    claim_in_xls_row::POWTORZ :
                    $rowki_dup++; break;
            }

            if ( $cls->status == claim_in_xls_row::POWTORZ)
            {
                $this->ws->getStyle('A' . $cls->rz . ':' . 'K' . $cls->rz)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFC0C0C0');
                $this->ws->getCell('A' . $cls->rz)->setValue( "Duplicated");
            }
        }

        $message = '';
        if ( $rowki_ok > 0)
            $message .= $rowki_ok . ' rows successfully imported' . PHP_EOL;
        if ( $rowki_warn > 0)
            $message .= $rowki_warn . ' rows imported with warnings' . PHP_EOL;
        if ( $rowki_blad > 0)
            $message .= $rowki_blad . ' rows ignored due to errors' . PHP_EOL;
        if ( $rowki_dup > 0)
            $message .= $rowki_dup . ' rows ignored due to duplication' . PHP_EOL;

        if ( $cls->status == claim_in_xls_row::TERMINATED)
            $message .= "Too many empty rows, import terminated". PHP_EOL;

        return $message;
    }
}
