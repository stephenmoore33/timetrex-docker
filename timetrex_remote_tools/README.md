# Installing TimeTrex Remote API Tools

TimeTrex On-Site server software includes all the necessary API tools to utilize the API. However, if you want to access the TimeTrex API from a remote computer or access the API of a TimeTrex Cloud Hosted account, please follow these instructions.

---

## Installation Steps

### Step 1

Download the TimeTrex Remote API Tools.

---

### Step 2

Ensure that you have PHP installed on the computer where you wish to run these tools.

#### Installing PHP on Windows

1. Download PHP for Windows here: [PHP Downloads](http://windows.php.net/download)
2. Extract the PHP files to a directory, such as `C:\php\`.
3. Rename the file `C:\php\php.ini-production` to `C:\php\php.ini`.
4. Edit the newly renamed `php.ini` file and uncomment the following lines by removing the semicolon (`;`) at the beginning of the line:

    ```ini
    extension=php_curl.dll
    extension=php_openssl.dll
    ```

---

### Step 3

Extract the `TimeTrex_Remote_Tools.zip` to `C:\timetrex_remote_tools`.

---

### Step 4

Register a permanent API key/Session ID, required for all remote tool usage. Follow these steps:

1. Login to TimeTrex as the employee that the API calls will be executed as.
2. In the blue header bar at the top right, click your profile to expand the Profile menu.
3. Select **Passwords / Security**.
4. Click the **More (...)** button at the top right of the inset page.
5. Select **Register API Key**.
6. Take note of the provided API key/Session ID for use below.

---

### Step 5

Once you have PHP installed and the TimeTrex tools extracted, you can see a list of arguments for each tool by running the tool with the help flag. Here is an example:

```bash
C:\php\php C:\timetrex_remote_tools\tools\import\import.php --help
