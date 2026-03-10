# Production Deployment

As of 2024-05-22, the current production deployment is on AWS EC2 using Amazon Linux 1 container image along with a MySQL RDS instance.

## SSH Access

To access the production server, your private key must be added to `~/.ssh/authorized_keys` on the server. Please ask a current admin to add your key.

```
ssh ec2-user@account.apobox.com
```

## Crontab

As of 2024-05-22, the following crontab is set up on the production server:
```
0 12 * * * cd /var/www/account.apobox.com && /var/www/account.apobox.com/bin/cake customer_reminder awaiting_payment > /dev/null
0 6 * * * cd /var/www/account.apobox.com && /var/www/account.apobox.com/bin/cake customer_reminder expired_cards > /dev/null
*/14 * * * * cd /var/www/account.apobox.com && /var/www/account.apobox.com/bin/cake Queue.Queue runworker > /dev/null
0 5 * * * sudo sh -c 'USE_PYTHON_3=1 /usr/local/bin/certbot-auto renew --debug --text >> /var/log/certbot/certbot-cron.log' && sudo service nginx reload
```

## Queue

A work queue script runs (via crontab) to process queued jobs. The processes terminate after 15 minutes. If they need to be cycled faster, run the following commands:
```
kill $(ps -elf | grep runworker | grep -v grep | cut -d' ' -f4)
cd /var/www/account.apobox.com && /var/www/account.apobox.com/bin/cake Queue.Queue runworker > /dev/null &
```

## Email configuration

Email is sent via SMTP through Google Mail service as admin@apobox.com (set in core.php).

### Troubleshooting

The email log can be viewed with:

```tail -f tmp/logs/email.log```

More info on errors: [SMTP Error Codes](https://support.google.com/a/answer/3221692)

### Changing email password

Occasionally, Google will reset the password on the email account, which requires generating a new app password.

Looking at the logs will show the following errors:
```
2024-05-22 13:25:25 Email-error: SMTP server did not accept the password. sending to: {"some.email@yahoo.com":"some user"}
```

To update the password, follow these steps:

#### Manager

1. Log into Google and go to https://myaccount.google.com/apppasswords
2. Create or change the app password
3. Provide the new password securely to the system administrator (use https://onetimesecret.com/ or similar to conceal it)

#### System Admin

1. Log into the production server (see [SSH Access](#ssh-access) above)
2. Edit the config with `sudo vim /var/www/account.apobox.com/Config/core-local.php`
3. Change the password at `Email.Transports.default.password`, save, and exit
4. Stop and restart the queue worker (optional, if you need faster than 15 minutes to restart, see [Queue Worker](#queue-worker) above)
5. Watch the logs for further errors with `tail -f tmp/logs/email.log`
