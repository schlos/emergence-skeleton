<?php

namespace Emergence\SiteAdmin;

use Site;
use Ifsnop\Mysqldump\Mysqldump;

class DatabaseRequestHandler extends \RequestHandler
{
    public static $userResponseModes = [
        'application/json' => 'json'
    ];

    public static function handleRequest()
    {
        $GLOBALS['Session']->requireAccountLevel('Developer');

        switch (static::peekPath()) {
            case 'dump.sql':
                return static::handleDumpRequest();
            default:
                return static::throwInvalidRequestError();
        }

        return static::handleBrowseRequest();
    }

    protected static function getConnectionConfig()
    {
        // load database config
        $config = Site::getConfig('database') ?: Site::getConfig('mysql');

        // build DSN string
        $dsn = 'mysql:';

        if (!empty($config['socket'])) {
            $dsn .= "unix_socket={$config['socket']}";
        } else {
            $dsn .= 'host=' . (empty($config['host']) ? 'localhost' : $config['host']);
            $dsn .= ';port=' . (empty($config['port']) ? '3306' : $config['port']);
        }

        $dsn .= ";dbname={$config['database']}";

        return [
            'dsn' => $dsn,
            'username' => $config['username'],
            'password' => $config['password']
        ];
    }

    public static function handleDumpRequest()
    {
        $connectionConfig = static::getConnectionConfig();

        // initialize dumper
        $dumper = new Mysqldump(
            $connectionConfig['dsn'],
            $connectionConfig['username'],
            $connectionConfig['password'],
            [
                'exclude-tables' => ['sessions', '_e_files', '_e_file_collections'],
                'skip-comments' => !isset($_GET['comments']),
                'skip-definer' => !isset($_GET['definer'])
            ]
        );

        // output dump
        header('Content-Type: application/sql');
        $dumper->start('php://output');
    }
}