<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('decisions:run')->hourly();
