import requests
import json

#Setup URL and API Key (Session ID)
TIMETREX_URL="https://demo.timetrex.com/api/json/api.php"
TIMETREX_API_KEY=""

#Define full URL that includes the class/method to call.
url = TIMETREX_URL + "?v=2&Class=APITimesheetDetailReport&Method=getTimesheetDetailReport&SessionID=" + TIMETREX_API_KEY

#Setup arguments for the API call as a numbered array. The first element of the array is the first argument to the method. The second element is the second argument, etc.
args = [
   {
        "time_period": {
            "time_period": "last_pay_period"
        },
        "columns": [
            "first_name",
            "last_name",
            "worked_time",
            "regular_time",
            "overtime_time",
            "absence_time",
            "premium_time"
        ],
        "group": [
            "first_name",
            "last_name"
        ],
        "sort": [
            {
                "last_name": "asc"
            },
            {
                "first_name": "asc"
            }
        ]
    },
    "csv"
]

#Make the API call, by encoding the arguments as json, prefixed with the "JSON" element name.
response = requests.post( url, data={ "json": json.dumps( args ) } )

#Print the response.
print(response.text)
