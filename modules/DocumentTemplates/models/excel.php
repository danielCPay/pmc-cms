<?php

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class zapytanie
{
    public ?zapytanie       $rodzic;
    public string           $modul;
    public array            $rowkiSzablonu;
    public \App\TextParser  $textParser;
    static int              $nowy_rowek;
    public nowyExcel        $wynik;
    public int              $rowki_to_del; // Używane tylko do list

    public function __construct( ?zapytanie $r, string $m)
    {
        $p = strchr( $m , 'MODULE=');
        if ( $p)
            $this->modul = trim(substr( $p, 7));
        else
            $this->modul = $m;

        $this->rodzic = $r;
        $this->rowki_to_del = -2;

        if ( !\App\Module::getModuleId( $this->modul))
            throw new Exception( "Bad module name in TEMPLATE");
    }
    public function zapytaj( int $id)
    {
        self::$nowy_rowek = 2;
        $this->pobierzRekord( $id);
        foreach ( $this->rowkiSzablonu as $rs)
            if ( is_a( $rs, "lista"))
                $rs->zapytaj( $id);
    }
    public function ustaw_rowki()
    {
        $this->wynik->zerujRowek( 1);
        foreach ( $this->rowkiSzablonu as $rs)
            if ( is_a( $rs, "lista"))
            {
                $rowki = $rs->ustaw_rowki();
                $nr = $rs->rowki_to_del + sizeof($rowki);

                if ($nr > 0)
                    $this->wynik->noweRowki(self::$nowy_rowek + 1, $nr);
                if ($nr < 0)
                    $this->wynik->noweRowki(self::$nowy_rowek - $nr, $nr);

                foreach ($rowki as $nr)
                {
                    $this->wynik->zerujRowek( self::$nowy_rowek);
                    $this->wynik->ustawRowek( self::$nowy_rowek++, $nr);
                }
            }
            else
            {
                $k = 0;
                foreach ($rs ?? [] as [ $kol, $war, $st])
                {
                    if (str_starts_with($war, "<<"))
                    {
                        $rs[$k]['1'] = $this->zamienPh($this->textParser, $war, $st);
                        $this->wynik->ustawKomorke(self::$nowy_rowek, $rs[$k]);
                    }

                    $k++;
                }

                self::$nowy_rowek++;
            }
    }
    public function pobierzRekord( int $id)
    {
        try
        {
            $this->textParser = \App\TextParser::getInstanceByModel(Vtiger_Record_Model::getInstanceById( $id, $this->modul));
        }
        catch( \App\Exceptions\AppException $ex)
        {
            if ( strstr( $ex->getMessage(), "ERR_RECORD_NOT_FOUND"))
            {
            $mn = Vtiger_Record_Model::getInstanceById( $id)->getModule()->getName();
            throw new Exception( "Mismatch between template list and record module names {$this->modul}/{$mn}");
        }
        else
            throw $ex;
        }
        if ( empty( $this->textParser))
            throw new Exception( "There is no record for " . $this->modul);
    }
    public function zamienPh( \App\TextParser $textParser, string $ph, array $st) : string
    {
        if ($st['numberFormat']['formatCode'] == "General")
            $tr = ' : ';
        else
        {
            $tr = 'Raw : ';
            $textParser->useRawPlaceholders = true;
        }

        $str = $textParser->setContent($ph)->parse()->getContent();

        if ( str_starts_with( trim( $str), "<<"))
        {
            $str = preg_replace('/\s+/', '', $str);

            $war = str_replace( [ '<', '>'], [''], $str);
            if ( strpos( $war, '.'))
                $war = substr( $war, 0, strpos( $war, '.'));
            if ( !$textParser->recordModel->getField( $war))
                return "Bad placeholder " . $war;

            if (strpos($str, '.'))
                $str = str_replace(['<<', '>>', '.'], ['$(relatedRecord' . $tr, ')$', '|'], $str);
            else
                $str = str_replace(['<<', '>>'], ['$(record' . $tr, ')$'], $str);

            $str = $textParser->setContent($str)->parse()->getContent();
        }
        if ( strchr( $st['numberFormat']['formatCode'], 'd'))
            {
            $data = \PhpOffice\PhpSpreadsheet\Shared\Date::stringToExcel($str);
            if ($data)
                $str = $data;
        }
        return $str;
    }
    public function dodajRowekSzablonu( $ci) : zapytanie
    {
        foreach ( $ci as $kom)
        {
            $war = $kom->getValue();
            if (!isset($war))
                continue;

            if (str_starts_with($war, 'DOTS LIST'))
            {
                $z = new lista( $this, $war);

                $this->rowkiSzablonu[] = $z ;
                return $z;
            }
            if (str_starts_with($war, '~LIST'))
            {
                $this->rodzic->rowki_to_del += $this->rowki_to_del;
                return $this->rodzic;
            }

            $komorki[] = [Coordinate::columnIndexFromString($kom->getColumn()), $war, $kom->getStyle()->exportArray()];
        }

        $this->rowkiSzablonu[] = $komorki ?? null;
        $this->rowki_to_del--;

        return $this;
    }
}
class lista extends zapytanie
{
    public array $rezultaty;
    public \App\QueryGenerator $qg;
    public string $pole;

    public function __construct( zapytanie $r, string $l)
    {
        $pocz = 1;
        $l = preg_replace( "/\s+/", " ", $l);
        if ( $pocz = strpos( $l , 'MODULE=', $pocz))
        {
            $k = strpos($l, ' ', $pocz + 7);
            $module = substr($l, $pocz + 7, $k - $pocz - 7);
        }

        if ( !isset( $module))
            throw new Exception( "There is no MODULE definition");

        parent::__construct($r, $module);

        $pocz = strpos( $l , 'FIELD=', $k );
        if ( $pocz)
        {
            $k = strpos($l, ' ', $pocz + 6);
            $this->pole = substr($l, $pocz + 6, $k - $pocz - 6);
        }
        else
            throw new Exception( "There is no FIELD definition");

        if ( !isset( $this->pole))
            throw new Exception( "There is no FIELD definition");

        if ( !isset( $this->modul))
            throw new Exception( "There is no TEMPLATE MODULE definition");

        preg_match('/FILTERING=(?<condition>.*?)($|[A-Z]+=)/', $l, $matches);
        $filter = null;
        if (!empty($matches['condition'])) {
            $filter = $matches['condition'];
        }

        $pocz = strpos($l, 'SORTING=', $k);
        $sort = null;
        if ( $pocz)
        {
            $k = strpos($l, '}', $pocz + 8);
            $sort = substr($l, $pocz + 8, $k - $pocz - 7);
        }

        foreach (\App\Relation::getByModule($this->rodzic->modul, true, $this->modul ) as $key => $relation)
            if (isset($this->pole) && $this->pole === $relation['field_name'])
            {
                $ok = 1;
                break;
            }
        if ( !isset( $ok))
            throw new Exception( "Something of these has incorrect value : " . $this->modul . ' ' . $this->pole);

        $this->dodajZapytanie( $filter, $sort);
    }
    public function dodajZapytanie( ?string $filter, ?string $sort)
    {
        $this->qg = (new \App\QueryGenerator($this->modul))->setField('id');
        if (isset($filter))
        {
            $cd = \App\Json::decode($filter);
            if ( empty( $cd['condition']))
            {
                $ncd = [ 'condition' => 'AND' , 'rules' => array()];
                $ncd['rules'][] = $cd;
                $cd = $ncd;
            }
            $this->qg->setConditions($cd);
        }
        if (isset($sort))
        {
            $or = \App\Json::decode($sort);
            if ( empty( $or['fieldname']) || empty( $or['order']))
                throw new Exception ( "Something wrong with sorting " . $sort);
            $this->qg->setOrder($or['fieldname'], $or['order']);
            $this->qg->setField($or['fieldname']);
        }

        if ( !isset( $this->qg))
            throw new Exception( "Something went wrong with constructing database query");
    }
    public function zapytaj( int $id)
    {
        $qg = clone $this->qg;
        $qg->addCondition( $this->pole, $id, 'eid');
        foreach( $qg->createQuery()->column() as $rez)
        {
            foreach ($this->rowkiSzablonu as $rs)
            {
                if (is_a($rs, "lista"))
                {
                    $rs = clone $rs;
                    $rs->zapytaj($rez);
                }

                $this->rezultaty[] = [ $rez, $rs];
            }
        }
    }
    public function ustaw_rowki() : array
    {
        $rr = [];

        foreach ( $this->rezultaty ?? [] as $rs)
            if (is_a($rs[1], "lista"))
                $rr = array_merge($rr, $rs[1]->ustaw_rowki());
            else
            {
                $this->pobierzRekord( $rs[0]);

                $k = 0;
                foreach ($rs[1] as [ $kol, $war, $st])
                {
                    if (str_starts_with($war, "<<"))
                        $rs[1][$k]['1'] = $this->zamienPh( $this->textParser, $war, $st);

                    $k++;
                }
                $rr[] = $rs[1];
            }

        return $rr;
    }
}
class nowyExcel
{
    public Spreadsheet $spreadsheet;
    public string $plikWynikowy = 'wynik.xlsx';
    public \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sh;

    public function zapiszWynik()
    {
        $writer  = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
            $writer->save($this->plikWynikowy);
        $this->spreadsheet->disconnectWorksheets();
    }
    public function __construct( string $plikSzablonu, string $pw)
    {
        $reader = new Xlsx();
        $this->spreadsheet = $reader->load($plikSzablonu);
        $this->plikWynikowy = $pw;
    }
    public function uaktualnijFormuly( $formula = '', $beforeCellAddress = 'A1', $numberOfColumns = 0, $numberOfRows = 0, $worksheetName = '')
    {
        //    Update cell references in the formula
        $formulaBlocks = explode('"', $formula);
        $i = false;
        $th = \PhpOffice\PhpSpreadsheet\ReferenceHelper::getInstance();
        foreach ($formulaBlocks as &$formulaBlock) {
            //    Ignore blocks that were enclosed in quotes (alternating entries in the $formulaBlocks array after the explode)
            if ($i = !$i) {
                $adjustCount = 0;
                $newCellTokens = $cellTokens = [];
                //    Search for row ranges (e.g. 'Sheet1'!3:5 or 3:5) with or without $ absolutes (e.g. $3:5)
                $matchCount = preg_match_all('/' . \PhpOffice\PhpSpreadsheet\ReferenceHelper::REFHELPER_REGEXP_ROWRANGE . '/i', ' ' . $formulaBlock . ' ', $matches, PREG_SET_ORDER);
                if ($matchCount > 0) {
                    foreach ($matches as $match) {
                        $fromString = ($match[2] > '') ? $match[2] . '!' : '';
                        $fromString .= $match[3] . ':' . $match[4];
                        $modified3 = substr($th->updateCellReference('$A' . $match[3], $beforeCellAddress, $numberOfColumns, $numberOfRows), 2);
                        $modified4 = substr($th->updateCellReference('$A' . $match[4], $beforeCellAddress, $numberOfColumns, $numberOfRows), 2);

                        if ($match[3] . ':' . $match[4] !== $modified3 . ':' . $modified4) {
                            if ( trim($match[2], "'") == $worksheetName) {
                                $toString = ($match[2] > '') ? $match[2] . '!' : '';
                                $toString .= $modified3 . ':' . $modified4;
                                //    Max worksheet size is 1,048,576 rows by 16,384 columns in Excel 2007, so our adjustments need to be at least one digit more
                                $column = 100000;
                                $row = 10000000 + (int) trim($match[3], '$');
                                $cellIndex = $column . $row;

                                $newCellTokens[$cellIndex] = preg_quote($toString, '/');
                                $cellTokens[$cellIndex] = '/(?<!\d\$\!)' . preg_quote($fromString, '/') . '(?!\d)/i';
                                ++$adjustCount;
                            }
                        }
                    }
                }
                //    Search for column ranges (e.g. 'Sheet1'!C:E or C:E) with or without $ absolutes (e.g. $C:E)
                $matchCount = preg_match_all('/' . \PhpOffice\PhpSpreadsheet\ReferenceHelper::REFHELPER_REGEXP_COLRANGE . '/i', ' ' . $formulaBlock . ' ', $matches, PREG_SET_ORDER);
                if ($matchCount > 0) {
                    foreach ($matches as $match) {
                        $fromString = ($match[2] > '') ? $match[2] . '!' : '';
                        $fromString .= $match[3] . ':' . $match[4];
                        $modified3 = substr($th->updateCellReference($match[3] . '$1', $beforeCellAddress, $numberOfColumns, $numberOfRows), 0, -2);
                        $modified4 = substr($th->updateCellReference($match[4] . '$1', $beforeCellAddress, $numberOfColumns, $numberOfRows), 0, -2);

                        if ($match[3] . ':' . $match[4] !== $modified3 . ':' . $modified4) {
                            if (trim($match[2], "'") == $worksheetName) {
                                $toString = ($match[2] > '') ? $match[2] . '!' : '';
                                $toString .= $modified3 . ':' . $modified4;
                                //    Max worksheet size is 1,048,576 rows by 16,384 columns in Excel 2007, so our adjustments need to be at least one digit more
                                $column = Coordinate::columnIndexFromString(trim($match[3], '$')) + 100000;
                                $row = 10000000;
                                $cellIndex = $column . $row;

                                $newCellTokens[$cellIndex] = preg_quote($toString, '/');
                                $cellTokens[$cellIndex] = '/(?<![A-Z\$\!])' . preg_quote($fromString, '/') . '(?![A-Z])/i';
                                ++$adjustCount;
                            }
                        }
                    }
                }
                //    Search for cell ranges (e.g. 'Sheet1'!A3:C5 or A3:C5) with or without $ absolutes (e.g. $A1:C$5)
                $matchCount = preg_match_all('/' . \PhpOffice\PhpSpreadsheet\ReferenceHelper::REFHELPER_REGEXP_CELLRANGE . '/i', ' ' . $formulaBlock . ' ', $matches, PREG_SET_ORDER);
                if ($matchCount > 0) {
                    foreach ($matches as $match) {
                        $fromString = ($match[2] > '') ? $match[2] . '!' : '';
                        $fromString .= $match[3] . ':' . $match[4];
                        $modified3 = $th->updateCellReference($match[3], $beforeCellAddress, $numberOfColumns, $numberOfRows);
                        $modified4 = $th->updateCellReference($match[4], $beforeCellAddress, $numberOfColumns, $numberOfRows);

                        if ($match[3] . $match[4] !== $modified3 . $modified4) {
                            if (trim($match[2], "'") == $worksheetName) {
                                $toString = ($match[2] > '') ? $match[2] . '!' : '';
                                $toString .= $modified3 . ':' . $modified4;
                                [$column, $row] = Coordinate::coordinateFromString($match[3]);
                                //    Max worksheet size is 1,048,576 rows by 16,384 columns in Excel 2007, so our adjustments need to be at least one digit more
                                $column = Coordinate::columnIndexFromString(trim($column, '$')) + 100000;
                                $row = (int) trim($row, '$') + 10000000;
                                $cellIndex = $column . $row;

                                $newCellTokens[$cellIndex] = preg_quote($toString, '/');
                                $cellTokens[$cellIndex] = '/(?<![A-Z]\$\!)' . preg_quote($fromString, '/') . '(?!\d)/i';
                                ++$adjustCount;
                            }
                        }
                    }
                }
                //    Search for cell references (e.g. 'Sheet1'!A3 or C5) with or without $ absolutes (e.g. $A1 or C$5)
                $matchCount = preg_match_all('/' . \PhpOffice\PhpSpreadsheet\ReferenceHelper::REFHELPER_REGEXP_CELLREF . '/i', ' ' . $formulaBlock . ' ', $matches, PREG_SET_ORDER);

                if ($matchCount > 0) {
                    foreach ($matches as $match) {
                        $fromString = ($match[2] > '') ? $match[2] . '!' : '';
                        $fromString .= $match[3];

                        $modified3 = $th->updateCellReference($match[3], $beforeCellAddress, $numberOfColumns, $numberOfRows);
                        if ($match[3] !== $modified3) {
                            if (trim($match[2], "'") == $worksheetName) {
                                $toString = ($match[2] > '') ? $match[2] . '!' : '';
                                $toString .= $modified3;
                                [$column, $row] = Coordinate::coordinateFromString($match[3]);
                                $columnAdditionalIndex = $column[0] === '$' ? 1 : 0;
                                $rowAdditionalIndex = $row[0] === '$' ? 1 : 0;
                                //    Max worksheet size is 1,048,576 rows by 16,384 columns in Excel 2007, so our adjustments need to be at least one digit more
                                $column = Coordinate::columnIndexFromString(trim($column, '$')) + 100000;
                                $row = (int) trim($row, '$') + 10000000;
                                $cellIndex = $row . $rowAdditionalIndex . $column . $columnAdditionalIndex;

                                $newCellTokens[$cellIndex] = preg_quote($toString, '/');
                                $cellTokens[$cellIndex] = '/(?<![A-Z\$\!])' . preg_quote($fromString, '/') . '(?!\d)/i';
                                ++$adjustCount;
                            }
                        }
                    }
                }
                if ($adjustCount > 0) {
                    if ($numberOfColumns > 0 || $numberOfRows > 0) {
                        krsort($cellTokens);
                        krsort($newCellTokens);
                    } else {
                        ksort($cellTokens);
                        ksort($newCellTokens);
                    }   //  Update cell references in the formula
                    $formulaBlock = str_replace('\\', '', preg_replace($cellTokens, $newCellTokens, $formulaBlock));
                }
            }
        }
        unset($formulaBlock);

        //    Then rebuild the formula string
        return implode('"', $formulaBlocks);
    }
    public function noweRowki( $gdzie, $ile)
    {
        foreach ( $this->spreadsheet->getWorksheetIterator() as $shi)
            if ( $shi !== $this->sh)
                foreach ( $shi->getRowIterator() as $rzi)
                {
                    $ci = $rzi->getCellIterator();
                    $ci->setIterateOnlyExistingCells( true);
                    foreach ( $ci as $kom)
                        if ( $kom->getDataType() == DataType::TYPE_FORMULA && strstr( $kom->getValue(), $this->sh->getTitle()))
                        {
                            $kom->setValue(
                                $this->uaktualnijFormuly(
                                    $kom->getValue(), 'A' . $gdzie, 0, $ile,
                                    $this->sh->getTitle()));
                        }
                }

        $this->sh->insertNewRowBefore( $gdzie, $ile);
    }
    public function ustawRowek(int $rz, array $komorki)
    {
        foreach ( $komorki as $kom)
        {
            $nk = $this->sh->getCellByColumnAndRow( $kom[0], $rz);
            $nk->setValue( $kom[1]);
            if ( isset( $kom[2]))
                $nk->getStyle()->applyFromArray( $kom[2]);
        }
    }
    public function ustawKomorke( int $rz, array $kom)
    {
        $nk = $this->sh->getCellByColumnAndRow( $kom[0], $rz);
        $nk->setValue( $kom[1]);
        $nk->getStyle()->applyFromArray( $kom[2]);
    }
    public function zerujRowek( $rz)
    {
        $ri = $this->sh->getRowIterator( $rz);
        $ci = $ri->current()->getCellIterator();
        $ci->setIterateOnlyExistingCells(true);
        foreach ( $ci as $c)
            $c->setValue( '');
    }
    public function zerujKomorke( $rz)
    {
        $this->sh->setCellValueByColumnAndRow( 1, $rz, '');
    }
    public function ustawSheet( int $i)
    {
        $this->sh = $this->spreadsheet->setActiveSheetIndex( $i);
    }
}
class excel
{
    public Spreadsheet $spreadsheet;

    public function przetworzSzablon(int $glowneId, string $plikSzablonu, string $plikWynikowy)
    {
        $reader = new Xlsx();
        $this->spreadsheet = $reader->load($plikSzablonu);

        $wynik = new nowyExcel($plikSzablonu, $plikWynikowy);

        $shi = 0;
        foreach ($this->spreadsheet->getAllSheets() as $sh)
        {
            $wynik->ustawSheet( $shi++);
            unset( $modul);
            foreach ( $sh->getRowIterator() as $rzi)
            {
                $rz = $rzi->getRowIndex();
                $ci = $rzi->getCellIterator();
                $ci->setIterateOnlyExistingCells(true);
                if ( isset( $modul))
                    $modul = $modul->dodajRowekSzablonu( $ci);
                else
                foreach ($ci as $kom)
                {
                    $war = $kom->getValue();
                    if (!isset($war))
                        continue;

                    if (str_starts_with($war, 'DOTS TEMPLATE'))
                        $modul = new zapytanie(null, $war);

                    break;
                }
            }
            if ( isset( $modul))
            {
                $modul->zapytaj($glowneId);
                $modul->wynik = $wynik;
                $modul->ustaw_rowki();
            }
        }
        $wynik->zapiszWynik();
    }
}