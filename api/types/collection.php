<?php

return function ($collection, $type, array $query = []) {

    $data = [];

    if (empty($query['paginate'])) {
        $query['paginate'] = [
            'page'  => 1,
            'limit' => 100
        ];
    }

    $collection = $collection->query($query);

    foreach ($collection as $item) {
        $data[] = $this->output($type, $item);
    }

    return [
        'pagination' => $this->output('pagination', $collection->pagination()),
        'items'      => $data
    ];

};
