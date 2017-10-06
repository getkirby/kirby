<?php

return function ($collection, $type) {

    $data = [];

    foreach ($collection as $item) {
        $data[] = $this->output($type, $item);
    }

    return [
        'pagination' => $this->output('pagination', $collection->pagination()),
        'data'       => $data
    ];

};
