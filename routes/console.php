<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:customer-reminders --partial-signups')->daily();
Schedule::command('app:customer-reminders --expiring-cards')->monthly();
Schedule::command('app:customer-reminders --purge-partials')->weekly();
