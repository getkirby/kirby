<?php

echo I18n::template('login.email.password-reset.body', null, compact('user', 'code', 'timeout'));
