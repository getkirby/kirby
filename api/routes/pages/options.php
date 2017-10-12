<?php

return [
    'pattern' => 'pages/(:all)/options',
    'action'  => function ($path) {

        $page    = $this->site()->find($path);
        $not     = explode(',', $this->input('not'));
        $options = [];

        // TODO: make this configurable
        if ($page->slug() === 'home' || $page->slug() === 'error') {
            $not[] = 'trash';
        }

        // preview
        if (in_array('preview', $not) === false) {
            $options[] = [
                'icon'  => 'preview',
                'text'  => 'Open preview',
                'link'  => '#',
                'click' => 'preview'
            ];
        }

        // status
        if (in_array('status', $not) === false) {
            $options[] = [
                'icon'  => 'toggle-on',
                'text'  => 'Status: Draft',
                'link'  => '#',
                'click' => 'status'
            ];
        }

        // url
        if (in_array('url', $not) === false) {
            $options[] = [
                'icon'  => 'chain',
                'text'  => 'Change URL',
                'link'  => '#',
                'click' => 'url'
            ];
        }

        // template
        if (in_array('template', $not) === false) {
            $options[] = [
                'icon'  => 'code',
                'text'  => 'Change Template',
                'link'  => '#',
                'click' => 'template'
            ];
        }

        // trash
        if (in_array('trash', $not) === false) {
            $options[] = [
                'icon'  => 'trash',
                'text'  => 'Delete this page',
                'link'  => '#',
                'click' => 'remove'
            ];
        }

        return $options;

    }
];
