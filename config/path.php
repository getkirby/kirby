<?php

return function () {
    return trim($this->server()->get('path_info'), '/');
};
