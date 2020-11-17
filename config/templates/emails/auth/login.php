<?php

echo I18n::template('login.email.login.body', null, compact('user', 'code', 'timeout'));
