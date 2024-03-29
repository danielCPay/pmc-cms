<?php
/* +**********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.1
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * Contributor(s): YetiForce.com
 * ********************************************************************************** */

class Vtiger_List_View extends Vtiger_Index_View
{
	protected $listViewEntries = false;
	protected $listViewCount = false;
	protected $listViewLinks = false;
	protected $listViewHeaders = false;

	/**
	 * List view model instance.
	 *
	 * @var Vtiger_ListView_Model
	 */
	protected $listViewModel;

	/**
	 * List view name or id.
	 *
	 * @var int|string
	 */
	protected $viewName;

	public function __construct()
	{
		parent::__construct();
	}

	public function getPageTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		$moduleName = 'Vtiger' === $moduleName ? 'YetiForce' : $moduleName;
		$title = App\Language::translate($moduleName, $moduleName);
		$title = $title . ' ' . App\Language::translate('LBL_VIEW_LIST', $moduleName);

		if ($request->has('viewname') && !empty(CustomView_Record_Model::getAll($moduleName)[$request->getByType('viewname', 2)])) {
			$customView = CustomView_Record_Model::getAll($moduleName)[$request->getByType('viewname', 2)];
			$title .= ' [' . App\Language::translate('LBL_FILTER', $moduleName) . ': ' . App\Language::translate($customView->get('viewname'), $moduleName) . ']';
		}
		return $title;
	}

	public function getBreadcrumbTitle(App\Request $request)
	{
		$moduleName = $request->getModule();
		$title = \App\Language::translate('LBL_VIEW_LIST', $moduleName);
		$fixedSearchParams = App\Condition::validSearchParams($moduleName, $request->getArray('fixed_search_params'));
		if (!empty($fixedSearchParams) && \is_array($fixedSearchParams) && $request->getArray('fixed_search_params') != [[[]]] && $request->getArray('fixed_search_params') != [[]]) {
			$fixedSearchParamsTitle = '[' . \App\Language::translate('LBL_FILTER_BY_PARENT', $moduleName) . ']';
		}
		if ($request->has('viewname') && !empty(CustomView_Record_Model::getAll($moduleName)[$request->getByType('viewname', 2)])) {
			$customView = CustomView_Record_Model::getAll($moduleName)[$request->getByType('viewname', 2)];
			$customViewTitle = '[' . \App\Language::translate('LBL_FILTER', $moduleName) . ': ' . \App\Language::translate($customView->get('viewname'), $moduleName) . ']';
		}
		if ($fixedSearchParamsTitle || $customViewTitle) {
			$newTitle = $fixedSearchParamsTitle;
			if ($fixedSearchParamsTitle && $customViewTitle) {
				$newTitle .= '/';
			}
			$newTitle .= $customViewTitle;
			$title .= '<div class="pl-1 pb-1 d-flex align-items-end"><small class="breadCrumbsFilter"> ' . $newTitle . '</small> </div>';
		}
		return $title;
	}

	/**
	 * Pre process.
	 *
	 * @param \App\Request $request
	 * @param bool         $display
	 */
	public function preProcess(App\Request $request, $display = true)
	{
		parent::preProcess($request, false);

		$moduleName = $request->getModule();
		$viewer = $this->getViewer($request);

		$mid = false;
		if ($request->has('mid')) {
			$mid = $request->getInteger('mid');
		}

		$linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->getByType('view', 1)];
		$viewer->assign('CUSTOM_VIEWS', CustomView_Record_Model::getAllByGroup($moduleName, $mid));
		$this->viewName = App\CustomView::getInstance($moduleName)->getViewId();
		if ($request->isEmpty('viewname') && App\CustomView::hasViewChanged($moduleName, $this->viewName)) {
			$customViewModel = CustomView_Record_Model::getInstanceById($this->viewName);
			if ($customViewModel) {
				App\CustomView::setSortBy($moduleName, $customViewModel->getSortOrderBy());
			}
			App\CustomView::setCurrentView($moduleName, $this->viewName);
		}
		$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $this->viewName);
		if (isset($_SESSION['lvs'][$moduleName]['entityState'])) {
			$this->listViewModel->set('entityState', $_SESSION['lvs'][$moduleName]['entityState']);
		}
		$viewer->assign('HEADER_LINKS', $this->listViewModel->getHederLinks($linkParams));
		$this->initializeListViewContents($request, $viewer);
		$viewer->assign('VIEWID', $this->viewName);
		$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
		if ($display) {
			$this->preProcessDisplay($request);
		}
	}

	public function preProcessTplName(App\Request $request)
	{
		return 'ListViewPreProcess.tpl';
	}

	protected function preProcessDisplay(App\Request $request)
	{
		parent::preProcessDisplay($request);
	}

	/**
	 * {@inheritdoc}
	 */
	public function process(App\Request $request)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();
		if ($request->isAjax()) {
			if (!isset($this->viewName)) {
				$this->viewName = App\CustomView::getInstance($moduleName)->getViewId();
			}
			$orderBy = $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL);
			if (App\CustomView::hasViewChanged($moduleName, $this->viewName)) {
				if ($orderBy || ($customViewModel = CustomView_Record_Model::getInstanceById($this->viewName))) {
					App\CustomView::setSortBy($moduleName, $orderBy ?: $customViewModel->getSortOrderBy());
				}
				App\CustomView::setCurrentView($moduleName, $this->viewName);
			} else {
				App\CustomView::setSortBy($moduleName, $orderBy);
				if ($request->has('page')) {
					App\CustomView::setCurrentPage($moduleName, $this->viewName, $request->getInteger('page'));
				}
			}
			if ($request->has('entityState')) {
				$_SESSION['lvs'][$moduleName]['entityState'] = $request->getByType('entityState');
			}
			$this->initializeListViewContents($request, $viewer);
			$viewer->assign('USER_MODEL', Users_Record_Model::getCurrentUserModel());
			$viewer->assign('MODULE_NAME', $moduleName);
			$viewer->assign('MODULE_MODEL', Vtiger_Module_Model::getInstance($moduleName));
			$viewer->assign('VIEWID', $this->viewName);
		}
		$viewer->assign('VIEW', $request->getByType('view', 1));
		$viewer->view('ListViewContents.tpl', $moduleName);
	}

	/**
	 * {@inheritdoc}
	 */
	public function postProcess(App\Request $request, $display = true)
	{
		$viewer = $this->getViewer($request);
		$moduleName = $request->getModule();

		$viewer->view('ListViewPostProcess.tpl', $moduleName);
		parent::postProcess($request);
	}

	/**
	 * Function to get the list of Script models to be included.
	 *
	 * @param \App\Request $request
	 *
	 * @return Vtiger_JsScript_Model[] - List of Vtiger_JsScript_Model instances
	 */
	public function getFooterScripts(App\Request $request)
	{
		$moduleName = $request->getModule();
		$jsFileNames = [
			'modules.Vtiger.resources.List',
			"modules.$moduleName.resources.List",
			'modules.CustomView.resources.CustomView',
			"modules.$moduleName.resources.CustomView",
			'modules.Vtiger.resources.ListSearch',
			"modules.$moduleName.resources.ListSearch",
		];

		return array_merge(parent::getFooterScripts($request), $this->checkAndConvertJsScripts($jsFileNames));
	}

	/**
	 * Function to initialize the required data in smarty to display the List View Contents.
	 *
	 * @param App\Request   $request
	 * @param Vtiger_Viewer $viewer
	 */
	public function initializeListViewContents(App\Request $request, Vtiger_Viewer $viewer)
	{
		$moduleName = $request->getModule();
		$pageNumber = $request->getInteger('page');
		$orderBy = $request->getArray('orderby', \App\Purifier::STANDARD, [], \App\Purifier::SQL);
		if (empty($orderBy) && !($orderBy = App\CustomView::getSortBy($moduleName))) {
			$moduleInstance = CRMEntity::getInstance($moduleName);
			if ($moduleInstance->default_order_by && $moduleInstance->default_sort_order) {
				$orderBy = [];
				foreach ((array) $moduleInstance->default_order_by as $value) {
					$orderBy[$value] = $moduleInstance->default_sort_order;
				}
			}
		}
		if (empty($pageNumber)) {
			$pageNumber = App\CustomView::getCurrentPage($moduleName, $this->viewName);
		}
		if (!$this->listViewModel) {
			$this->listViewModel = Vtiger_ListView_Model::getInstance($moduleName, $this->viewName);
		}
		if (!$request->isEmpty('searchResult', true)) {
			$this->listViewModel->set('searchResult', $request->getArray('searchResult', 'Integer'));
		}
		$linkParams = ['MODULE' => $moduleName, 'ACTION' => $request->getByType('view', 'Alnum'), 'CVID' => $this->viewName];
		$linkModels = $this->listViewModel->getListViewMassActions($linkParams);
		$pagingModel = new Vtiger_Paging_Model();
		$pagingModel->set('page', $pageNumber);
		$pagingModel->set('viewid', $this->viewName);
		if (!empty($orderBy)) {
			$this->listViewModel->set('orderby', $orderBy);
		}
		$operator = 's';
		if (!$request->isEmpty('operator', true)) {
			$operator = $request->getByType('operator');
			$this->listViewModel->set('operator', $operator);
			$viewer->assign('OPERATOR', $operator);
		}
		if (!$request->isEmpty('search_key', true)) {
			$searchKey = $request->getByType('search_key', 'Alnum');
			$searchValue = App\Condition::validSearchValue($request->getByType('search_value', 'Text'), $moduleName, $searchKey, $operator);
			$this->listViewModel->set('search_key', $searchKey);
			$this->listViewModel->set('search_value', $searchValue);
			$viewer->assign('ALPHABET_VALUE', $searchValue);
		}
		if ($request->has('entityState')) {
			$this->listViewModel->set('entityState', $request->getByType('entityState'));
		}
		$searchParams = App\Condition::validSearchParams($moduleName, $request->getArray('search_params'));
		if (!empty($searchParams) && \is_array($searchParams)) {
			$transformedSearchParams = $this->listViewModel->getQueryGenerator()->parseBaseSearchParamsToCondition($searchParams);
			$this->listViewModel->set('search_params', $transformedSearchParams);
			//To make smarty to get the details easily accesible
			foreach ($request->getArray('search_params') as $fieldListGroup) {
				$searchParamsRaw[] = $fieldListGroup;
				foreach ($fieldListGroup as $fieldSearchInfo) {
					$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
					$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
					$fieldSearchInfo['specialOption'] = \in_array($fieldSearchInfo[1], ['ch', 'kh']) ? true : '';
					$searchParams[$fieldName] = $fieldSearchInfo;
				}
			}
		} else {
			$searchParamsRaw = $searchParams = [];
		}
		$fixedSearchParams = App\Condition::validSearchParams($moduleName, $request->getArray('fixed_search_params'));
		if (!empty($fixedSearchParams) && \is_array($fixedSearchParams)) {
			$transformedFixedSearchParams = $this->listViewModel->getQueryGenerator()->parseBaseSearchParamsToCondition($fixedSearchParams);
			$currentSearchParams = $this->listViewModel->get('search_params');
			if (!empty($currentSearchParams)) {
				if ($transformedFixedSearchParams['and']) {
					foreach($transformedFixedSearchParams['and'] as $condition) {
						$currentSearchParams['and'][] = $condition;
					}
				}
			} else {
				$currentSearchParams = $transformedFixedSearchParams;
			}
			$this->listViewModel->set('search_params', $currentSearchParams);
			//To make smarty to get the details easily accesible
			foreach ($request->getArray('fixed_search_params') as $fieldListGroup) {
				$fixedSearchParamsRaw[] = $fieldListGroup;
				foreach ($fieldListGroup as $fieldSearchInfo) {
					$fieldSearchInfo['searchValue'] = $fieldSearchInfo[2];
					$fieldSearchInfo['fieldName'] = $fieldName = $fieldSearchInfo[0];
					$fieldSearchInfo['specialOption'] = \in_array($fieldSearchInfo[1], ['ch', 'kh']) ? true : '';
					$fixedSearchParams[$fieldName] = $fieldSearchInfo;
				}
			}
		}	else {
			$fixedSearchParamsRaw = $fixedSearchParams = [];
		}
		if (!$this->listViewHeaders) {
			$this->listViewHeaders = $this->listViewModel->getListViewHeaders();
		}
		if (!$this->listViewEntries) {
			try {	
				$this->listViewEntries = $this->listViewModel->getListViewEntries($pagingModel);
			} catch (\Exception $e) {
				\App\Log::error("Vtiger::View::List:error in initializeListViewContents - " . $e->getMessage());
				\App\Log::error(var_export($e, true));

				$this->listViewEntries = [];
				$viewer->assign('LIST_QUERY_ERROR', 1);
			}
		}
		$noOfEntries = \count($this->listViewEntries);
		$viewer->assign('MODULE', $moduleName);
		if (!$this->listViewLinks) {
			$this->listViewLinks = $this->listViewModel->getListViewLinks($linkParams);
		}
		$viewer->assign('LISTVIEW_LINKS', $this->listViewLinks);
		$viewer->assign('LISTVIEW_MASSACTIONS', $linkModels['LISTVIEWMASSACTION'] ?? []);
		$viewer->assign('PAGING_MODEL', $pagingModel);
		$viewer->assign('PAGE_NUMBER', $pageNumber);
		$viewer->assign('ORDER_BY', $orderBy);
		$viewer->assign('LISTVIEW_ENTRIES_COUNT', $noOfEntries);
		$viewer->assign('LISTVIEW_HEADERS', $this->listViewHeaders);
		$viewer->assign('LISTVIEW_ENTRIES', $this->listViewEntries);
		$totalCount = false;
		if (App\Config::performance('LISTVIEW_COMPUTE_PAGE_COUNT')) {
			if (!$this->listViewCount) {
				$this->listViewCount = $this->listViewModel->getListViewCount();
			}
			$pagingModel->set('totalCount', (int) $this->listViewCount);
			$totalCount = (int) $this->listViewCount;
		}
		$viewer->assign('LISTVIEW_COUNT', $totalCount);
		$viewer->assign('PAGE_COUNT', $pagingModel->getPageCount());
		$viewer->assign('START_PAGIN_FROM', $pagingModel->getStartPagingFrom());
		$viewer->assign('VIEW_MODEL', $this->listViewModel);
		$viewer->assign('IS_MODULE_EDITABLE', $this->listViewModel->getModule()->isPermitted('EditView'));
		$viewer->assign('IS_MODULE_DELETABLE', $this->listViewModel->getModule()->isPermitted('Delete'));
		$viewer->assign('SEARCH_DETAILS', $searchParams);
		$viewer->assign('SEARCH_PARAMS', $searchParamsRaw);
		$viewer->assign('FIXED_SEARCH_DETAILS', $fixedSearchParams);
		$viewer->assign('FIXED_SEARCH_PARAMS', $fixedSearchParamsRaw);
	}
}
