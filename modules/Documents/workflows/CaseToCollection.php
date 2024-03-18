<?php
// $GLOBALS['dc'] = 0;
// $GLOBALS['dp'] = 0;
// $GLOBALS['dv'] = 0;

class sprawa
{
    public string $id;
    public string $case_id;
    public string $number;
    public string $claim;
    public string $org_claim;
    public string $org_policy;
    public string $org_provider;
    public array $policies;
    public string $provider;
    public string $first_name;
    public string $last_name;

    public function __construct( array $a)
    {
        $this->id = $a['id'];
        $this->case_id = $a['case_id'] ?? $a['outside_case_id'];
        $fin = strtoupper( $a['insuredInsuredsinsured1_first_name']);
        $fin = explode( ' ', $fin);
        $this->first_name = $fin[0];
        $this->last_name = strtoupper( $a['insuredInsuredsinsured1_last_name']);
        $this->org_claim = $a['claim_number'];
        $this->claim = ltrim( strtoupper( preg_replace( '/[^a-z0-9A-Z]+/i', '', $this->org_claim)), "0");
        $this->org_policy = $a['policy_number'];
        $policy = strtoupper( preg_replace( '/[^a-z0-9A-Z ]+/i', ' ', $this->org_policy));
        $this->org_provider = $a['providerProvidersprovider_name'] ?? "";
        $pn = strtoupper( preg_replace( '/[\s+]/', ' ', $this->org_provider));
        $pn = strtoupper( preg_replace( '/[^a-z0-9A-Z ]+/i', '', $pn));
        $px = explode( ' ', $pn);
        $this->provider = ( $px[0] ?? "") . ( $px[1] ?? "");

        $pl = explode( ' ', $policy);
        $this->policies[] = str_replace( ' ', "", $policy);
        if ( sizeof( $pl) == 3)
        {
            $ls0 = strlen( $pl[0]);
            $ls2 = strlen( $pl[2]);

            if ( $ls0 < 4 && strspn($pl[0], "QWERTYUIOPASDFGHJKLZXCVBNM") == $ls0 && strspn($pl[2], "0123456789") == $ls2 && $ls2 < 3)
            {
                $this->policies[] = $pl[0] . $pl[1];
                $this->policies[] = $pl[1] . $pl[2];
                $tr = ltrim($pl[1] . $pl[2], '0');
                if ($tr != end($this->policies))
                    $this->policies[] = $tr;
                $this->policies[] = $pl[1];
                $tr = ltrim($pl[1], '0');
                if ($tr != end($this->policies))
                        $this->policies[] = $tr;
                }
            }
            if ( sizeof( $pl) == 2 && strspn( $pl[1], "0123456789") < 3 && strlen( $pl[1]) < 3)
            {
                $this->policies[] = $pl[0];
                $tr = ltrim( $pl[0], '0');
                if ( $tr != end( $this->policies))
                    $this->policies[] = $tr;
            }
        }
        public function __toString() : string
        {
            return $this->id;
        }
    }
    class sprawy
    {
        public array $cases;
        public array $outsideCases;
        public function czytaj()
        {
            $qg = new \App\QueryGenerator( 'Cases');
            $qg->setField( [ 'id', 'case_id', 'claim_number', 'policy_number']);
            $qg->addRelatedField( [ 'sourceField' => 'provider', 'relatedModule' => 'Providers', 'relatedField' => 'provider_name']);
            $qg->addRelatedField( [ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'insured1_first_name']);
            $qg->addRelatedField( [ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'insured1_last_name']);
            $qg->setOrder( "claim_number", "ASC");
            $rez = $qg->createQuery()->all();

            foreach ( $rez as $rz)
            {
                $c = new sprawa( $rz);
                $this->cases[$c->claim][] = $c;
            }

            $qg = new \App\QueryGenerator( 'OutsideCases');
            $qg->setField( [ 'id', 'outside_case_id', 'claim_number', 'policy_number']);
            $qg->addRelatedField( [ 'sourceField' => 'provider', 'relatedModule' => 'Providers', 'relatedField' => 'provider_name']);
            $qg->addRelatedField( [ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'insured1_first_name']);
            $qg->addRelatedField( [ 'sourceField' => 'insured', 'relatedModule' => 'Insureds', 'relatedField' => 'insured1_last_name']);
            $qg->setOrder( "claim_number", "ASC");
            $rez = $qg->createQuery()->all();

            foreach ( $rez as $rz)
            {
                $c = new sprawa( $rz);
                $this->cases[$c->claim][] = $c;
                $this->outsideCases[] = $rz['id'];
            }
        }
    }
    class linia_w_czeku
    {
        public array $wspolrzedne;
        public string $txt;

        public function __construct( $l1)
        {
            $l = $l1->boundingBox;
            $cosf = rozpoznawanie_czeku::$cosf;
            $sinf = rozpoznawanie_czeku::$sinf;

            $nl[0] = $l[0]*$cosf - $l[1]*$sinf;
            $nl[1] = $l[0]*$sinf + $l[1]*$cosf;
            $nl[2] = $l[2]*$cosf - $l[3]*$sinf;
            $nl[3] = $l[2]*$sinf + $l[3]*$cosf;
            $nl[4] = $l[4]*$cosf - $l[5]*$sinf;
            $nl[5] = $l[4]*$sinf + $l[5]*$cosf;
            $nl[6] = $l[6]*$cosf - $l[7]*$sinf;
            $nl[7] = $l[6]*$sinf + $l[7]*$cosf;
            $this->wspolrzedne[] = [$nl[0], $nl[1]];
            $this->wspolrzedne[] = [$nl[2], $nl[3]];
            $this->wspolrzedne[] = [$nl[4], $nl[5]];
            $this->wspolrzedne[] = [$nl[6], $nl[7]];

            $this->txt = strtoupper( $l1->text);
            /*
            if ( !str_starts_with( $l, "([["))
                throw new Exception( );

            $pos = explode( ", ", substr( $l, 1));
            $i = 0;
            $txt = '';
            foreach ( $pos as $p)
            {
                if ( $i < 8)
                {
                    if ($i % 2 == 1)
                    {
                        if ( !str_ends_with($p, ']'))
                            throw new Exception();
                        $y = str_replace("]", "", $p);
                        $this->wspolrzedne[] = [ $x, $y];
                    }
                    if ($i % 2 == 0)
                    {
                        if ( !str_starts_with($p, '['))
                            throw new Exception();
                        $x = str_replace("[", "", $p);
                    }
                    $i++;
                    continue;
                }

                $txt .= $p;
                if ( str_ends_with( $txt, "'"))
                    $this->txt = strtoupper( trim( $txt, "'"));
            }
            if ( !isset( $this->txt))
                throw new Exception();
            */
        }
    }
    class rozpoznawanie_czeku
    {
        public array $linie;
        public array $claims;
        public array $policies;
        public array $providers;
        public array $insureds;
        public array $checks;
        public ?string $check_value1;
        public ?string $check_value2;
        public ?string $check_number;
        public sprawy $spr;
        public ?string  $assigned_case_id;
        public int  $assigned_case;
        public static float $cosf;
        public static float $sinf;

        public function __construct( sprawy $s)
        {
            $this->spr = $s;
        }
        public function init_provider( )
        {
            $prev = "";
            $this->providers = [];
            foreach ( $this->linie as $l2)
                foreach ( explode( " ", $l2->txt) as $l)
            {
                $l2 = $prev  . $l;
                $l2 = strtoupper( preg_replace( '/[\s+]/', '', $l2));
                $this->insureds[] = strtoupper( $l);
                $l2 = strtoupper( preg_replace( '/[^a-z0-9 ]+/i', '', $l2));
                $this->providers[] = $l2;
                $l2 = strtoupper( preg_replace( '/[^0-9,.]+/i', '', $l));
                if ( str_contains( $l2, '.'))
                    $this->checks[] = $l2;
                $prev = $l;
            }
        }
        public function normalizuj_claim( string $c)  : array
        {
            $pot_cl2 = [];
            foreach ( explode( " " ,$c) as $c2)
                    if ( strlen( $c2) > 4 && $c2 != "NUMBER")
                        foreach ( str_split( $c2) as $c)
                            if ( IntlChar::isdigit( $c))
                            {
                                $c2 = preg_replace( '/[^a-z0-9A-Z ]+/i', '', $c2);
                                $sr = str_replace( "O", "0", $c2);
                                if ( $sr != $c2)
                                {
                                    $c2 = ltrim( $c2, "0");
                                    $pot_cl2[] = $c2;
                                }
                                $sr = ltrim( $sr, "0");
                                $pot_cl2[] = $sr;
                                $sr2 = str_replace( '$', 'S', $sr);
                                if ( $sr2 != $sr)
                                    $pot_cl2[] = $sr2;
                                break;
                            }
            return $pot_cl2;
        }
        public function normalizuj_polise( string $c)  : array
        {
            $s = preg_replace('/[^0-9A-Z\s]/', "", $c);
            $s = preg_replace('/[\s+]/', ' ', $s);
            $at = explode( " " ,$s);
            $at2 = [];
            foreach ( $at as $a)
                if ( $a != "CLAIM" && $a != "NUMBER" && $a != "POLICY" && $a != "POL #")
                    $at2[] = $a;
            if ( empty( $at2))
                return [];
            $s = implode( " ", $at2);
            $ret[] = $s;
            $ret[] = preg_replace('/[\s+]/', ' ', $s);
            $ret[] = $s;
            $ret[] = ltrim($s, "0");
            $ret[] = str_replace("O", "0", $s);

            $arr = explode(" ", $s);
            $s = sizeof($arr);
            if ($s > 1)
            {
                $ret[] = $arr[0] . $arr[1];
                if ( strlen( $arr[0]) > 4)
                {
                    $ret[] = $arr[0];
                    $ret[] = ltrim($arr[0], "0");
                }
                if ( strlen( $arr[1]) > 4)
                {
                    $ret[] = $arr[1];
                    $ret[] = ltrim($arr[1], "0");
                }
            }
            if ($s > 2)
            {
                $ret[] = $arr[0] . $arr[1];
                $ret[] = $arr[1] . $arr[2];
                $ret[] = $arr[2];
                $ret[] = ltrim($arr[1], "0");
            }

            $rtt = [];
            foreach ( $ret as $r)
                foreach ( str_split( $r) as $c)
                    if ( IntlChar::isdigit( $c))
                    {
                        $rtt[] = $r;
                        break;
                    }

            return $rtt;
        }

        public function znajdz( array $cot, array &$zap, ?string $notCont)
        {
            $zn1 = [];
            foreach ( $this->linie as $l)
            {
                if ( strlen( $l->txt) < 5)
                    continue;

                // echo $l->wspolrzedne[0][0] . " " . $l->wspolrzedne[0][1] .  " " . $l->txt . PHP_EOL;

                foreach ( $cot as $co)
                {
                    if (str_contains($l->txt, $co) && !str_contains($l->txt, $notCont ?? "%%##@@"))
                    {
                        $h = abs($l->wspolrzedne[3][1] - $l->wspolrzedne[0][1]) / 3;
                        $w = $l->wspolrzedne[1][0] - $l->wspolrzedne[0][0];
                        $posx = $l->wspolrzedne[1][0];
                        $posy = $l->wspolrzedne[0][1];
                        $zn1[] = $l->txt;
                        continue;
                    }
                    if (isset($posx))
                    {
                        foreach ($this->linie as $li)
                            if (abs($li->wspolrzedne[0][1] - $posy) < 2 * $h)
                                $zn1[] = $li->txt;
                            else
                                if ($li->wspolrzedne[0][1] - $posy > $h &&
                                    $li->wspolrzedne[0][1] - $posy < 9 * $h &&
                                    abs($li->wspolrzedne[0][0] - $posx) < 2 * $w)
                                {
                                    $zn1[] = $li->txt;
                                }
                    }
                }
            }

            $pot_cl2 = [];

            foreach ( $zn1 as $c)
                if ( str_starts_with( $cot[0], "POL"))
                    $pot_cl2 = array_merge( $pot_cl2, $this->normalizuj_polise( $c));
                else
                    $pot_cl2 = array_merge( $pot_cl2, $this->normalizuj_claim( $c));

                /*
            foreach ( $zn1 as $c)
                foreach ( explode( " ", $c) as $c2)
                    if ( strlen( $c2) > 4 && $c2 != "NUMBER")
                        foreach ( str_split( $c2) as $c)
                            if ( IntlChar::isdigit( $c))
                            {
                                $sr = str_replace( "O", "0", $c2);
                                if ( $sr != $c2)
                                {
                                    $c2 = ltrim(str_replace('-', "", $c2), "0");
                                    $pot_cl2[] = $c2;
                                }
                                $sr = ltrim( str_replace( '-', "", $sr), "0");
                                $pot_cl2[] = $sr;
                                $sr2 = str_replace( '$', 'S', $sr);
                                if ( $sr2 != $sr)
                                    $pot_cl2[] = $sr2;
                                break;
                            }
                */

            $zap = array_unique( $pot_cl2);
        }
        public function sprawdz_coll( int $id)
        {
            $qg = new \App\QueryGenerator( 'Collections');
            $qg->setField( [ 'case', 'value', 'check_number', 'payment_method']);
            $qg->addRelatedField( [ 'sourceField' => 'case', 'relatedModule' => 'Cases', 'relatedField' => 'case_id']);
            $qg->addCondition( 'id' ,$id, 'e');
            $rez = $qg->createQuery()->all();
            foreach ( $rez as $r)
            {
                $this->check_number = $r['check_number'] ?? null;
                $this->check_value1 = $r['value'] ? number_format( $r['value'], 2) : null;
                $this->check_value2 = $r['value'] ? number_format( $r['value'], 2,  '.', '') : null;
                $pm = $r['payment_method'];

                $this->assigned_case = $r['case'];
                $this->assigned_case_id = $r['caseCasescase_id'];
                if ( $pm != "Check")
                    throw new Exception( "Paid not with a check");

                return;
            }
            throw new Exception( "Collection " . $id . " not found");
        }
        public function czytaj_rezultat( string $p, string $f) : bool
        {
            if ($file = fopen($p . $f, "r"))
            {
                $line = fgets($file);
                fclose($file);
            }
            else
                throw new Exception("File with check not found");

            $json = json_decode($line);
            if ($json->status != "succeeded")
            {
                \App\Log::warning('OCR status ' . $json->status);
                return true;
            }
            $ls = $json->analyzeResult->readResults[0]->lines;

            rozpoznawanie_czeku::$cosf = cos( deg2rad( -$json->analyzeResult->readResults[0]->angle));
            rozpoznawanie_czeku::$sinf = sin( deg2rad( -$json->analyzeResult->readResults[0]->angle));

            foreach ( $ls as $l)
                $this->linie[] = new linia_w_czeku( $l);

            if ( empty( $this->linie))
                throw new Exception( "No data read from the check file");

            return false;
        }
        public function rozpocznij_ocr( $file) : string
        {
            try
            {
                $client = new \GuzzleHttp\Client();
                $checkf = file_get_contents($file);

                if ( getenv( "INSTALL_MODE") == "PROD")
                {
                    $subsk = "ca5690ade9ba4b54816bcc03fc7e9e4d";
                    $host = "https://pmc-com-vis-eu.cognitiveservices.azure.com";
                }
                else
                {
                    $subsk = "c031b66a9ede4e999ea2f510dca62fb5";
                    $host = "https://pdss-com-vis-eu.cognitiveservices.azure.com";
                }

                $response = $client->request(
                    'POST',
                    $host . '/vision/v3.2/read/analyze',
                    [
                        'headers' => [
                            'Ocp-Apim-Subscription-Key' => $subsk,
                            'Content-Type' => 'Application/octet-stream'
                        ],
                        'body' => $checkf]
                );

                $headers = $response->getHeaders();
    

                $url = $headers["Operation-Location"][0];
                if (empty($url))
                    throw new Exception("Empty operation-Location");

            }
            catch (\GuzzleHttp\Exception\BadResponseException $ex)
            {
                sleep( 3);
                throw new Exception( $ex->getResponse()->getBody());
            }

            return $url;
        }
        public function pobierz_ocr( string $at, string $url)
        {
            try
            {
                $client = new \GuzzleHttp\Client();

                $rsp = "Unknown error";
                if ( getenv( "INSTALL_MODE") == "PROD")
                    $subsk = "ca5690ade9ba4b54816bcc03fc7e9e4d";
                else
                    $subsk = "c031b66a9ede4e999ea2f510dca62fb5";

                for ($i = 3; $i < 8; $i++)
                {
                    sleep($i);
                    $response = $client->request(
                        'GET',
                        $url,
                        [
                            'headers' => [
                                'Ocp-Apim-Subscription-Key' => $subsk,
                                'Content-Type' => 'Application/octet-stream'
                            ]
                        ]
                    );
                    $rsp = $response->getBody() ?? "ERR";
                    if (str_starts_with($rsp, '{"status":"succeeded",'))
                    {
                        file_put_contents("/tmp/ocr" . $at . ".json", $rsp);
                        break;
                    }
                }
                if ($i == 8)
                    throw new Exception($rsp);
            }
            catch(  \GuzzleHttp\Exception\BadResponseException $ex)
            {
                throw new Exception($ex->getResponse()->getBody());
            }
        }
        public function rozpoznaj() : string
        {
            $this->claims = [];
            $this->policies = [];
            $this->init_provider();

            $this->znajdz( ["CLAIM", "USAA #:"], $this->claims, "CLAIMANT");
            $this->znajdz( [ "POLICY", "POL #"], $this->policies, "POLICYHOLDER");

            foreach ( $this->policies as $pl)
                if ( strspn( $pl, "QWERTYUIOPASDFGHJKLZXCVBNM") == 3)
                    $pl2[] = substr( $pl, 3);

            $this->policies = array_merge( $this->policies, $pl2 ?? []);
            foreach ( $this->checks ?? [] as $i)
                if ( $i == $this->check_value1 || $i == $this->check_value2)
                {
                    $cvf = false;
                    break;
                }

            return ( $cvf ?? true) ? ( "Check value " . $this->check_value1 . " not found in the image file\n" ) : "";

            /*
            if ( !empty( $this->check_number))
            {
                unset( $cvf);
                foreach ($this->insureds as $i)
                    if ($i == $this->check_number)
                    {
                        $cvf = true;
                        break;
                    }

                $msg .= "Check number not found in image file";
            }

            return $msg ?? "";
            */
        }
        public function ustaw_case( int $clid, int $csid, string $msg) : string
        {
            $coll = Vtiger_Record_Model::getInstanceById( $clid, 'Collections');
            if ( in_array( $csid, $this->spr->outsideCases))
            {
                $coll->set('outside_case', $csid);
                $coll->set( 'case', 0);
            }
            else
            {
                $coll->set('outside_case', 0);
                $coll->set('case', $csid);
            }

            $coll->save();
            $st = "";
            if ( $this->assigned_case != 0 && $this->assigned_case != $csid)
                $st = "Previous case " . $this->assigned_case_id . "; ";

            if ( $this->assigned_case != 0)
                if ( $csid == 0)
                    $st .= "Case Id unlinked\n";
                else
                    $st .= "New ";

            return $st . $msg;
        }
        public function dopasuj( string &$msg, int &$cid)
        {
            $clDokladny = false;
            $plDokladna = false;
            $sprawy = [];
            $cl = $this->dopasuj_claim($clDokladny, $sprawy);
            $provm = "";
            $polm = "";
            $pv = $this->dopasuj_provider($sprawy, $provm);
            unset( $insm);
            if ( empty( $this->policies))
            {
                if ( $this->dopasuj_insured($sprawy))
                    $insm = $pl = $plDokladna = true;
                else
                    $plDokladna = $pl = false;
            }
            else
            {
                $pl = $this->dopasuj_polise($plDokladna, $sprawy, $polm);
                if ( $pl == false || $plDokladna == false)
                    if ( $this->dopasuj_insured($sprawy))
                        $insm = true;
            }

            if ( $cl == true && $pv == true && $pl == true && $clDokladny == true && $plDokladna == true)
            {
                $spr = $sprawy[0];
                $msg = "Case#: " . $spr->case_id;
                $msg .= "\nClaim#: " . $spr->org_claim . " matched\n";

                if ( $provm == 'FLORIDA INSURANCE')
                    $msg .= "Check for FLORIDA INSURANCE\n";
                else
                    $msg .= "Provider: " . $spr->org_provider . " matched\n";

                if ( !empty( $polm))
                    $msg .= "Policy#: " . $spr->org_policy . " matched\n";
                elseif ( !empty( $insm))
                    $msg .= "Insured: " . $spr->first_name . " " . $spr->last_name . " matched\n";
                $cid = $spr->id;
                return;
            }

            if ( sizeof( $sprawy) == 1)
            {
                $spr = $sprawy[0];
                $msg = "Suggested Case Id " . $spr->case_id . "\n";

                if ( $cl == true && $clDokladny == true)
                    $msg .= "Claim#: " . $spr->org_claim . " matched\n";
                else
                    $msg .= "Claim#: " . $spr->org_claim . " not matched\n";
                if ( $pv == false)
                    $msg .= "Provider: " . $spr->org_provider . " not matched\n";
                else
                    $msg .= "Provider: " . $spr->org_provider . " matched\n";
                if ( !empty( $this->policies))
                {
                    if ($pl == false || $plDokladna == false)
                    {
                        $msg .= "Policy#: " . $spr->org_policy . " not matched\n";
                        if ( !empty( $insm))
                            $msg .= "Insured: " . $spr->first_name . " " . $spr->last_name . " matched\n";
                        else
                            $msg .= "Insured: " . $spr->first_name . " " . $spr->last_name . " not matched\n";
                    }
                    else
                        $msg .= "Policy#: " . $spr->org_policy . " matched\n";
                }
                else
                {
                    if ($pl == false)
                        $msg .= "Insured: " . $spr->first_name . " " . $spr->last_name . " not matched\n";
                    else
                        $msg .= "Insured: " . $spr->first_name . " " . $spr->last_name . " matched\n";
                }
            }
            else
                $msg = "Nothing was matched";
        }
        public function dopasuj_provider( array &$sprawy, string &$msg) : bool
        {
            $rs = [];
            foreach ( $sprawy as $sp)
                foreach ( $this->providers as $pv)
                    if ( $sp->provider == $pv)
                    {
                        $rs[] = $sp;
                        $msg = $pv;
                        break;
                    }

            if ( !empty( $rs))
            {
                $sprawy = $rs;
                return true;
            }
            if ( sizeof( $sprawy) == 1)
                foreach ( $this->providers as $pv)
                    if ( $pv == "FLORIDAINSURANCE")
                    {
                        $msg = 'FLORIDA INSURANCE';
                        return true;
                    }

            return false;
        }
        public function dopasuj_insured( array &$sprawy) : bool
        {
            $rs = [];
            foreach ( $sprawy as $sp)
            {
                $pos =  array_search($sp->first_name, $this->insureds);
                if ( $pos != false)
                {
                    $pos2 =  array_search($sp->last_name, $this->insureds, $pos);
                    if ( $pos2 != false && $pos2 - $pos < 3)
                        $rs[] = $sp;

                }
            }
            if ( !empty( $rs))
            {
                $sprawy = $rs;
                return true;
            }
            return false;
        }
        public function dopasuj_polise( bool &$dokladny, array &$sprawy, string &$msg) : bool
        {
            $rs = [];
            foreach ( $this->policies as $pa)
                foreach ( $sprawy as $sa)
                    foreach ( $sa->policies as $pl)
                {
                    if ($pl == $pa)
                    {
                        $dokladny = true;
                        $msg = $pa;
                        $rs[] = $sa;
                    }
                }

            if ( $dokladny == true)
            {
                $sprawy = $rs;
                return true;
            }

            $dokladny = false;
            $min = 100;

            foreach ( $this->policies as $pa)
                foreach ( $sprawy as $sp1)
                foreach ( $sp1->policies as $sp)
                {
                    $ml = levenshtein( $pa, $sp);
                    if ($ml < $min)
                    {
                        $min = $ml;
                        $rs = [];
                        $rs[] = $sp1;
                    }
                    elseif ( $ml == $min)
                        $rs[] = $sp1;
                }

            if ( $min < 4)
            {
                $sprawy = array_unique( $rs);
                return true;
            }
            return false;
        }
        public function dopasuj_claim( bool &$dokladny, array &$sprawy) : bool
        {
            foreach ( $this->claims as $cl)
            {
               $sprawy = $this->spr->cases[$cl] ?? null;
                if (isset($sprawy))
                {
                    $dokladny = true;
                    return true;
                }
            }

            $min= 100;
            $sprawy = [];

            foreach ( $this->claims as $cl)
                foreach ($this->spr->cases as $sp)
                {
                    $ml = levenshtein($cl, $sp[0]->claim, 1, 5, 10);
                    if ($ml < $min)
                    {
                        $min = $ml;
                        $sprawy = [];
                        $sprawy = array_merge( $sprawy, $sp);
                    }
                    elseif ( $ml == $min)
                        $sprawy = array_merge( $sprawy, $sp);
                }

            $sprawy = array_unique( $sprawy);
            $dokladny = false;
            if ( $min < 4)
                return true;
            else
            {
                $sprawy = [];
                foreach ( array_values( $this->spr->cases) as $c)
                    $sprawy = array_merge( $sprawy, $c);

                return false;
            }
        }
    }

    class CaseToCollection
    {
        public static function assignCaseToCollection( Vtiger_Record_Model $recordModel)
        {
            \App\Log::warning( 'CaseToCollection::assignCaseToCollection');

            $path = $recordModel->getFileDetails()['path'];
            $fn = $recordModel->get('filename');
            $at = $recordModel->getFileDetails()['attachmentsid'];

            $coll = \App\Request::_get( 'collection') ?? $recordModel->get('collection');
            if ( empty( $coll))
                $coll = $recordModel->get('collection');

            \App\Log::warning( 'CaseToCollection::collectionId :' . $coll . " path: " . $path . " fn: " . $fn . " at: " . $at);

            if ( empty( $coll))
            {
                $recordModel->set( 'verification_warnings', "Collection to assign not set");
                $recordModel->save();
                return;
            }

            try
            {
                $spr = new sprawy();
                $spr->czytaj();

                $s = new rozpoznawanie_czeku( $spr);

                $s->sprawdz_coll( $coll);
                $status = "";

                for ( $i = 0; $i < 3; $i++)
                    try
                    {
                        $url = $s->rozpocznij_ocr( $path . $at);
                        $s->pobierz_ocr( $at, $url);
                        break;
                    }
                    catch( Exception $ex)
                    {
                        \App\Log::warning( 'CaseToCollection::ocrException : ' . $status = $ex->getMessage());
                        sleep( ($i+1)*15);
                    }

                if ( $i == 3)
                {
                    $recordModel->set( 'verification_warnings', $status);
                    $recordModel->set( 'last_date_of_parsing', date('Y-m-d H:i:s'));
                    $recordModel->save();
                    return;
                }

                // $s->czytaj_rezultat( $path, $at);
                $s->czytaj_rezultat( "/tmp/ocr", $at . ".json");
                $checkmsg = $s->rozpoznaj();
                $msg = "";
                $id = 0;
                $s->dopasuj($msg, $id);
                $status .= $s->ustaw_case((int)$coll, $id, $msg);
                $recordModel->set('case', $id);
            }
            catch ( Throwable $ex)
            {
                $status = $ex->getMessage();
            }

            // $recordModel->set( 'verification_warnings', $status . ( $checkmsg ?? false ? (" + " . $checkmsg) : null));
            $recordModel->set( 'verification_warnings', $checkmsg . $status);
            $recordModel->set( 'last_date_of_parsing', date('Y-m-d H:i:s'));
            $recordModel->save();
        }
    }
