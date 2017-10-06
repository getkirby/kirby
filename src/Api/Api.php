<?php

namespace Kirby\Api;

use Exception;

use GraphQL\Schema;
use GraphQL\GraphQL;

use Kirby\Api\Query;
use Kirby\Api\Type;

class Api
{

    protected $queries;
    protected $query;
    protected $variables;
    protected $data;

    public function __construct(array $params)
    {
        $this->query     = $params['query'] ?? null;
        $this->variables = $params['variables'] ?? null;
        $this->data      = $params['data'] ?? [];

        // move this outside the API class
        $this->queries = [
            'site'      => require __DIR__ . '/Query/SiteQuery.php',
            'page'      => require __DIR__ . '/Query/PageQuery.php',
            'pages'     => require __DIR__ . '/Query/PagesQuery.php',
            'children'  => require __DIR__ . '/Query/ChildrenQuery.php',
            'file'      => require __DIR__ . '/Query/FileQuery.php',
            'files'     => require __DIR__ . '/Query/FilesQuery.php',
            'user'      => require __DIR__ . '/Query/UserQuery.php',
            'users'     => require __DIR__ . '/Query/UsersQuery.php',
            'language'  => require __DIR__ . '/Query/LanguageQuery.php',
            'languages' => require __DIR__ . '/Query/LanguagesQuery.php',
        ];

        $this->mutations = [
            'createPage' => require __DIR__ . '/Mutation/CreatePageMutation.php',
            'updatePage' => require __DIR__ . '/Mutation/UpdatePageMutation.php',
            'deletePage' => require __DIR__ . '/Mutation/DeletePageMutation.php',

            // 'deletePage' => '',
            // 'createFile' => '',
            // 'updateFile' => '',
            // 'deleteFile' => '',
            // 'createUser' => '',
            // 'updateUser' => '',
            // 'deleteUser' => '',
        ];


        Type::set([
            // output
            'site'       => require __DIR__ . '/Type/Output/SiteOutput.php',
            'page'       => require __DIR__ . '/Type/Output/PageOutput.php',
            'pages'      => require __DIR__ . '/Type/Output/PagesOutput.php',
            'field'      => require __DIR__ . '/Type/Output/FieldOutput.php',
            'file'       => require __DIR__ . '/Type/Output/FileOutput.php',
            'files'      => require __DIR__ . '/Type/Output/FilesOutput.php',
            'users'      => require __DIR__ . '/Type/Output/UsersOutput.php',
            'user'       => require __DIR__ . '/Type/Output/UserOutput.php',
            'avatar'     => require __DIR__ . '/Type/Output/AvatarOutput.php',
            'pagination' => require __DIR__ . '/Type/Output/PaginationOutput.php',
            'language'   => require __DIR__ . '/Type/Output/LanguageOutput.php',

            // input
            'pagesQueryInput' => require __DIR__ . '/Type/Input/PagesQueryInput.php',
            'filesQueryInput' => require __DIR__ . '/Type/Input/FilesQueryInput.php',
            'paginationInput' => require __DIR__ . '/Type/Input/PaginationInput.php',
            'filterInput'     => require __DIR__ . '/Type/Input/FilterInput.php',
            'fieldInput'      => require __DIR__ . '/Type/Input/FieldInput.php',
            'pageInput'       => require __DIR__ . '/Type/Input/PageInput.php',
        ]);

    }

    public function schema()
    {
        return new Schema([
            'query'    => new Query($this->queries, $this->data),
            'mutation' => new Mutation($this->mutations, $this->data)
        ]);
    }

    public function result()
    {
        try {
            return GraphQL::execute(
                $this->schema(),
                $this->query,
                null,
                null,
                (array)$this->variables
            );
        } catch(Exception $e) {
            return [
                'error' => [
                    'message' => $e->getMessage()
                ]
            ];
        }
    }
}
