# Dockerized TimeTrex Community Edition v16.12.9

This repository provides a Dockerized version of TimeTrex Community Edition v16.12.9, the last open-source release of TimeTrex. It includes all required configurations and tools for easy deployment, including support for PostgreSQL as the backend database.

## Features

- TimeTrex Community Edition v16.12.9 in a Dockerized environment.
- Environment variables for full configuration flexibility.
- Compatible with PostgreSQL (version 8 or newer required).
- Includes TimeTrex Remote Tools, downloaded from TimeTrex CoreAPI.

## Prerequisites

- Docker and Docker Compose installed.
- A PostgreSQL database version 8 or newer.

## Environment Variables

The `timetrex.php.ini` configuration has been mapped to the following `.env` variables. Update these values in your `.env` file as needed:

| Variable                 | Default Value           | Description                                   |
|--------------------------|-------------------------|-----------------------------------------------|
| BASE_URL                | <http://localhost:80>     | Base URL of the TimeTrex application.        |
| HOST_NAME               | localhost               | Hostname of the server.                      |
| URL_POSTFIX             | /timetrex/interface     | Postfix URL for TimeTrex.                    |
| ADMIN_EMAIL             | <admin@example.com>     | Administrator's email address.               |
| DB_HOST                 | db                      | PostgreSQL database host.                    |
| DB_NAME                 | timetrex                | PostgreSQL database name.                    |
| DB_USER                 | timetrex                | PostgreSQL username.                         |
| DB_PASS                 | timetrex                | PostgreSQL password.                         |
| EMAIL_DELIVERY_METHOD   | smtp                    | Email delivery method (`smtp` recommended).  |
| EMAIL_SMTP_HOST         | smtp.gmail.com          | SMTP server address.                         |
| EMAIL_SMTP_PORT         | 587                     | SMTP server port.                            |
| EMAIL_SMTP_USERNAME     | <timetrex@gmail.com>      | SMTP username for authentication.            |
| EMAIL_SMTP_PASSWORD     | testpass123             | SMTP password for authentication.            |
| EMAIL_DOMAIN            | mydomain.com            | Domain for email delivery.                   |
| EMAIL_LOCAL_PART        | DoNotReply              | Local part for email address.                |
| PRODUCTION              | TRUE                    | Set to `TRUE` for production mode.           |
| FORCE_SSL               | FALSE                   | Set to `TRUE` to enforce SSL.                |
| ENABLE_CSRF_VALIDATION  | FALSE                   | Set to `TRUE` to enable CSRF validation.     |
| DISABLE_AUTO_UPGRADE    | TRUE                    | Set to `TRUE` to disable auto-upgrades.      |

## Getting Started

### Clone the Repository

```bash
git clone https://github.com/yourusername/timetrex-docker.git
cd timetrex-docker
```

### Set Up `.env` File

Create a `.env` file in the repository root and configure the variables as needed. Use the defaults provided above or customize them for your setup.

### Run Docker Compose

Start the Dockerized environment using:

```bash
docker-compose up -d
```

## TimeTrex Remote Tools

This repository also includes TimeTrex Remote Tools, which can be found in the `timetrex_remote_tools` directory. These tools were obtained from the official source: TimeTrex CoreAPI.

## License

The TimeTrex Community Edition v16.12.9 is licensed under the GNU Affero General Public License (AGPL) v3. This repository complies with the AGPL by including the source code for TimeTrex Community Edition v16.12.9.

Any additional files or scripts in this repository, including the Docker configuration, are licensed under the AGPL v3 unless otherwise noted.

## Contributing

Contributions are welcome! Feel free to fork this repository, make your changes, and submit a pull request.

## Disclaimer

This project is not affiliated with, maintained by, or endorsed by TimeTrex. It is provided "as is" without warranty of any kind. Use it at your own risk.
