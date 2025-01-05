# TimeTrex Command Line Tools

## Example Usages

### Importing Employees

Before using the import tool, we highly recommend going through an import within the TimeTrex application at least once to make it easier to map your import file columns.

To do this:

1. First login to the TimeTrex application.
2. Click `Company -> Import` in the menu along the top of the screen.
3. This will bring up the import wizard which will step you through the import process. Be sure to save the map settings on the column mapping step.

> **Warning:** If you go through the entire import wizard on your live installation, this will import data into your TimeTrex application.

Once you have saved the column mapping with the import wizard, you will be able to export it to be used with the command line.

Here is an example of exporting a saved mapping named "Default" with the import type "User":

```bash
C:\php\php C:\timetrex_remote_tools\tools\import\import.php -server https://demo.timetrex.com/api/json/api.php -api_key YourAPIKeyHere -object User -export_map Default C:\Temp\default_employees.map
```

You should now be able to import employees using the newly exported mapping file with a command similar to this:

```bash
C:\php\php C:\timetrex_remote_tools\tools\import\import.php -server https://demo.timetrex.com/api/json/api.php -api_key YourAPIKeyHere -object User C:\Temp\default_employees.map C:\temp\data_to_import.csv
```

If you want to update existing employees and import new employees in a single operation, use a command similar to this that specifies the `-f update` argument:

```bash
C:\php\php C:\timetrex_remote_tools\tools\import\import.php -server https://demo.timetrex.com/api/json/api.php -api_key YourAPIKeyHere -object User -f update C:\Temp\default_employees.map C:\temp\data_to_import.csv
```

### Importing Punches

Importing punches can be accomplished in a similar way as importing employee records described above. Follow the instructions for exporting a mapping file, then you can import punches with a command similar to this:

```bash
C:\php\php C:\timetrex_remote_tools\tools\import\import.php -server https://demo.timetrex.com/api/json/api.php -api_key YourAPIKeyHere -object Punch C:\Temp\punches.map C:\temp\data_to_import.csv
```

### Exporting Reports

We recommend that you first create a saved report from the TimeTrex application that is customized as needed. Then you can simply export that report by name from the command line. Keep in mind saved reports are only accessible by the employee that creates them, so you must use the same username/password when exporting reports.

If the type of report you saved was a TimeSheet Summary report, with the name "MySavedReport", you would export that report using the following example:

```bash
C:\php\php C:\timetrex_remote_tools\tools\export\export_report.php -server https://demo.timetrex.com/api/json/api.php -api_key YourAPIKeyHere -report TimesheetSummaryReport -saved_report MySavedReport C:\temp\timesheet_summary_report.csv csv
```

If you need to filter the report based on a specific start/end date rather than using the relative time period such as "Last Pay Period", see this example:

```bash
C:\php\php C:\timetrex_remote_tools\tools\export\export_report.php -server https://demo.timetrex.com/api/json/api.php -api_key YourAPIKeyHere -report TimesheetSummaryReport -saved_report MySavedReport -time_period custom_date -filter start_date=01-Jan-19,end_date=31-Jan-19 C:\temp\timesheet_summary_report.csv csv
```

### Using the API Directly

If you wish to utilize the API directly to programmatically perform actions within TimeTrex that are not available from the existing tools, please see our https\://www.timetrex.com/workforce-management-api.
