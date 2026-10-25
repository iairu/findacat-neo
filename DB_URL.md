Hello, The official work-around that seems to work is to append the endpoint_ID with the password in the connection string like

DATABASE_URL=postgres://dbuser:<endpoint=<endpoint_id>>;<your_password>@ep-broad-snowflake-sdexc.il-central-1.aws.neon.tech/neondb?pgbouncer=true&connect_timeout=10
So basically, add this --> endpoint=<endpoint_id>; (including the comma)

Alternatively, if you are using database connection parameters, you can use the following

...
DB_PASSWORD='<endpoint=<endpoint_id>>;<your_password>'
In our case, the endpoint ID is --> ep-broad-snowflake-sdexc (as seen in the connection URL above)

Hope this helps.
