<?php


use PhpOffice\PhpSpreadsheet\IOFactory;


class Documents_activityReports_Cron extends \App\CronHandler
{
    const SERVICE_NAME = 'LBL_ACTIVITY_REPORTS';
    public $spreadsheet;
    public $doct = 2343;
    public function znajdz_brakujace_dokumenty( &$bd)
    {
        $date = new DateTime();
        $date->sub( DateInterval::createFromDateString('30 day'));
        $fd = $date->format( "Y-m-d");
        $qg = new \App\QueryGenerator('Documents');
        $qg->addCondition("document_type", $this->doct, "eid");
        $qg->addCondition("createdtime", $fd, "g");
        $rez = $qg->createQuery()->all();

        $cd = array();
        foreach ($rez as $r)
            if ( str_starts_with( $r['title'], "Activity"))
                $cd[] = substr($r['title'], 16, 10);

        for ( $i = 0; $i < 30; $i++)
        {
            $bd[] = $date->format( "m/d/Y");
            $date->add( DateInterval::createFromDateString('1 day') );
        }

        $bd = array_diff( $bd, $cd);
        sort( $bd);
    }
    public  function znajdz_typ_dokumentu()
    {
        $qg = new \App\QueryGenerator('DocumentTypes');
        $qg->addCondition("document_type", "Activity Report", "e");
        $rez = $qg->createQuery()->all();

        foreach ($rez as $r)
        {
            $this->doct = $r['documenttypesid'];
            break;
        }
    }
    public  function init_spreadcheet()
    {
        $this->spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $ws = $this->spreadsheet->getSheet( 0);
        $ws->getCell( 'A1')->setValue( 'User Name');
        $ws->getCell( 'B1')->setValue( 'Changed on');
        $ws->getCell( 'C1')->setValue( 'Operation');
        $ws->getCell( 'D1')->setValue( 'Module Label');
        $ws->getCell( 'E1')->setValue( 'Item Label');
        $ws->getCell( 'F1')->setValue( 'Field Label');
        $ws->getCell( 'G1')->setValue( 'New Value');
        $ws->getCell( 'H1')->setValue( 'crmid');
        $ws->getCell( 'A1')->getStyle()->getFont()->setBold( true);
        $ws->getCell( 'B1')->getStyle()->getFont()->setBold( true);
        $ws->getCell( 'C1')->getStyle()->getFont()->setBold( true);
        $ws->getCell( 'D1')->getStyle()->getFont()->setBold( true);
        $ws->getCell( 'E1')->getStyle()->getFont()->setBold( true);
        $ws->getCell( 'F1')->getStyle()->getFont()->setBold( true);
        $ws->getCell( 'G1')->getStyle()->getFont()->setBold( true);
        $ws->getCell( 'H1')->getStyle()->getFont()->setBold( true);

        $ws->getColumnDimension( 'A')->setAutoSize( true);
        $ws->getColumnDimension( 'B')->setAutoSize( true);
        $ws->getColumnDimension( 'C')->setAutoSize( true);
        $ws->getColumnDimension( 'D')->setAutoSize( true);
        $ws->getColumnDimension( 'E')->setAutoSize( false);
        $ws->getColumnDimension( 'F')->setAutoSize( true);
        $ws->getColumnDimension( 'G')->setWidth( 160, 'pt');
        $ws->getColumnDimension( 'H')->setWidth( 16, 'pt');
    }
    public  function zapisz( $dzien)
    {
        $this->spreadsheet->getActiveSheet()->setAutoFilter(
            $this->spreadsheet->getActiveSheet()
                ->calculateWorksheetDimension()
        );

        $tn = tempnam("/tmp", "aktyw_xlsx");

        $writer = IOFactory::createWriter($this->spreadsheet, 'Xlsx');
        $writer->save($tn);
        $this->spreadsheet->disconnectWorksheets();

        $params = ['document_type' => $this->doct];

        $file = \App\Fields\File::loadFromPath($tn);
        $file->name = 'Activity Report ' . $dzien . ".xlsx";
        \App\Fields\File::saveFromContent($file, $params);
        \App\Log::warning( 'ActivityReports::zapisano ? ' . $dzien);
    }
    public  function zapytanie( $dzien)
    {
        $query = (new \App\Db\Query())->select(['user_name', 'changed_on', 'operation',
            'module_label', 'item_label', 'field_label', 'new_value', 'crmid'])->from('vw_activity_log')
            ->where([ '=', "DATE_FORMAT(changed_on, '%m/%d/%Y')", $dzien])->batch();

        $nr = 2;
        $ws = $this->spreadsheet->getSheet( 0);

        foreach ( $query as $rows)
            foreach ( $rows as $row)
        {
            $ws->getCell( 'A' . $nr)->setValue( $row['user_name']);
            $ws->getCell( 'B' . $nr)->setValue( $row['changed_on']);
            $ws->getCell( 'C' . $nr)->setValue( $row['operation']);
            $ws->getCell( 'D' . $nr)->setValue( $row['module_label']);
            $ws->getCell( 'E' . $nr)->setValue( $row['item_label']);
            $ws->getCell( 'F' . $nr)->setValue( $row['field_label']);
		if ( str_starts_with( $nw = $row['new_value'] ?? "", "="))
			$nw = '"' . $nw;
            $ws->getCell( 'G' . $nr)->setValue( $nw);
            $ws->getCell( 'H' . $nr)->setValue( $row['crmid']);
            $nr++;
        }
    }

    public function process()
    {
        $service = \App\Request::_get('service');
        if ($service !== self::SERVICE_NAME) 
            return;

        \App\Log::warning( 'ActivityReports::process F-' . memory_get_usage( false) . " T-" . memory_get_usage( true));
        \App\User::setCurrentUserId(\App\User::getUserIdByName('---'));
        $bd = array();
        $this->znajdz_typ_dokumentu();
        $this->znajdz_brakujace_dokumenty( $bd);
        \App\Log::warning( 'ActivityReports::przetworz rozmiar ' . sizeof( $bd));
        foreach ( $bd as $dzien)
        {
            // echo " A " . memory_get_usage( true) . " " . memory_get_usage( false) . " " . memory_get_peak_usage( true) . " " . memory_get_peak_usage( false) . PHP_EOL;

            \App\Log::warning( 'ActivityReports::przetworz ' . memory_get_usage( true));
            $this->init_spreadcheet();
            $this->zapytanie( $dzien);
            \App\Log::warning( 'ActivityReports::przetworz-zapytanie ' . $dzien);
            $this->zapisz( $dzien);
        }
    }
}
