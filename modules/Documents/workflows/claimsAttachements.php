<?php

require_once 'pobierz_zalacznik.php';

class claimsAttachements
{
    public $relationModelCl;
    public array $docTypes;
    public array $rowki = array();
    public array $kolumny = array();
    public array $odczyt_rowki = array();
    static public claimsAttachements $instance_;

    public function __construct()
    {
        claimsAttachements::$instance_ = $this;
        $this->relationModelCl = Vtiger_Relation_Model::getInstance(
            Vtiger_Module_Model::getInstance('Claims'),
            Vtiger_Module_Model::getInstance('Documents')
        );

        $qg = new \App\QueryGenerator('DocumentTypes');
        $qg->addCondition("document_area", "Claims Management", "e");
        $rez = $qg->createQuery()->all();

        foreach ($rez as $r)
        {
            $s = $r['document_type'];
            $sp = strtolower(preg_replace("/\s+/", "", $s));
            $this->docTypes[$sp] = $r['documenttypesid'];
        }

        $qg = new \App\QueryGenerator('DocumentTypes');
        $qg->addCondition("document_type", "AOB", "e");
        $rez = $qg->createQuery()->all();

        foreach ($rez as $r)
        {
            $s = $r['document_type'];
            $sp = strtolower(preg_replace("/\s+/", "", $s));
            $this->docTypes[$sp] = $r['documenttypesid'];
        }
    }
    public function czyNaglowekAtt( int $kol, ?string $nag) : bool
    {
        $s = strtolower(preg_replace("/\s+/", "", $nag));
        if ( array_search( $s, array_keys( $this->docTypes)))
        {
            $this->kolumny[$kol] = $s;
            return true;
        }

        return false;
    }
    public function czyKolumnaAtt( int $rowNr, int $kolNr, string $var) : bool
    {
        if ( !in_array( $kolNr, array_keys( $this->kolumny)))
        {
            if (str_starts_with($var, "https://"))
            {
                $this->rowki[$rowNr][$kolNr] = ["otherproviderdocs", $var];
                return true;
            }

            return false;
        }

        $dt = $this->kolumny[$kolNr];
        $this->rowki[$rowNr][$kolNr] = [ $dt, $var];
        return true;
    }
    public function znormalizujLinkDropbox( $url) : string
    {
        return str_replace( "dl=0", "dl=1", $url);
    }
    public static function znormalizujLinkGoogleDocs( $url) : string
    {
        if ( ( $pos = strpos(  $url, '/d/')) === false)
            return "invalid link";

        if ( ( $pos = strpos( $url, '/', $pos + 3)) === false)
            return $url . '/export?format=pdf';

        return substr( $url, 0, $pos) . '/export?format=pdf';
    }
    public function znormalizujLinkGoogle( $url) : string
    {
        $skl = explode( "/", $url);
        foreach ( $skl as $s)
            if (strlen($s) > 12 && !str_contains($s, "google.com"))
            {
                $id = $s;
                break;
            }
        if ( isset( $id))
            return 'https://drive.google.com/uc?export=download&id=' . $id;

        return "invalid link";
    }
    public function pobierz( string $url) : array
    {
        if ( str_contains( $url, "dropbox.com"))
            $url = claimsAttachements::znormalizujLinkDropbox( $url);
        elseif ( str_contains( $url, "docs.google.com"))
            $url = claimsAttachements::znormalizujLinkGoogleDocs( $url);
        elseif ( str_contains( $url, "google.com"))
            $url = claimsAttachements::znormalizujLinkGoogle( $url);

        $plik = pobierz_zalacznik::pobierz($url);
        if ( !empty( $plik[0]))
            return $plik;

        $res = tempnam( "/tmp", "dropbox");
        if ( empty( file_put_contents( $res, $plik[1])))
            return ["Invalid attachement file OR file error"];

        return [ "", $res, $plik[2]];
    }
    public function  przygotujURLe( string $kom) : array
    {
        $ta = explode( "http" , $kom);
        $ret = array();
        $stat = null;
        foreach ( $ta as $u)
        {
            if (empty($u))
                continue;

            $url = 'http' . trim($u);
            $s = $this->pobierz($url);
            if (!empty($s[0]))
            {
                if ( !empty( $stat))
                    $stat .= PHP_EOL;
                $stat .= $s[0] . " " . $url;
                continue;
            }

            $ret[] = [ $s[1], $s[2]];
        }

        if ( empty( $stat))
            $stat = "ok";

        return array_merge( [$stat], $ret);
    }
    public function zapisz( $idClaim, $nazwaAt, $doct, $orgNazwa)
    {
        $params = ['document_type' => $doct];
        $params[$this->relationModelCl->getRelationField()->getName()] = $idClaim;

        $file = \App\Fields\File::loadFromPath($nazwaAt);
        $file->name = $orgNazwa;
        ['crmid' => $fileId, 'attachmentsId' => $attachmentId] = \App\Fields\File::saveFromContent($file, $params);
    }
    public function zapiszWierszAtt( int $idClaim, $xcl)
    {
        if ( in_array( $xcl->rz, $this->odczyt_rowki))
            return;

        $this->odczyt_rowki[] = $xcl->rz;

        foreach ( $this->rowki[$xcl->rz] ?? [] as $kn => $k)
        {
            if ( empty( $k[1]))
                continue;

            $urls = $this->przygotujURLe( $k[1]);
            if ( $urls[0] != "ok")
            {
                if ( !empty( $xcl->status_kol))
                    $xcl->status_kol .= PHP_EOL;
                $xcl->status_kol .= $urls[0];
            }

            for ( $i = 1; $i < sizeof( $urls); $i++)
                $this->zapisz( $idClaim, $urls[$i][0], $this->docTypes[$k[0]], $urls[$i][1]);
        }
    }
}
