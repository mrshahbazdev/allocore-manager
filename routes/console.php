<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('decisions:run')->hourly();
Schedule::command('data:prune')->daily();
