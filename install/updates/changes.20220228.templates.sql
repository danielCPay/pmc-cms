SELECT
  -- workflow_id, task_id, summary, templateid, t.emailtemplatesid, t.number,
  CONCAT('update com_vtiger_workflowtasks set task = replace(task, ''s:', LENGTH(templateid), ':"', templateid, '"'', ''s:', LENGTH(NUMBER), ':"', NUMBER, '"'') where task_id = ', task_id, ' and workflow_id = ', workflow_id, ';')
FROM
  (
    SELECT 
      task_id, workflow_id, summary, REGEXP_REPLACE(task, '^.*template";s:[0-9]+:"([0-9]+)".*$', '\\1') templateid
    FROM 
      com_vtiger_workflowtasks 
    WHERE 
      task RLIKE 'VTEmailTemplate' OR task RLIKE 'VTSend'
  ) tsk
  LEFT JOIN u_yf_emailtemplates t ON ( t.emailtemplatesid = tsk.templateid )
