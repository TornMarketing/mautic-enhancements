<?php
/**
 * Execute Mautic console commands.
 *
 * This script can be used in environments where you do not have SSH access
 * to run the Mautic command line tool "console"
 *
 * @abstract    Script to run Mautic (mautic.org) commands from a web page.
 * @copyright   2019 Virgil. All rights reserved
 * @version     0.1.6
 * @date        2019-10-20
 * @author      Virgil <virgil@virgilwashere.co>
 * @license     GPL3
 * @param       string $secretphrase    URL parameter: passphrase to limit execution of commands
 * @param       string $pretty          URL parameter
 * @var         string $cdn             https://cdn.jsdelivr.net/gh/virgilwashere/mautic-cron-commands
 * @var         string $backarrow       img src
 * @var         string $logo            img src
 * @var         string $mautibot        img src
 * @var         string $server_name     HTTP header SERVER_NAME
 * @var         string $docroot         Path to Mautic root
 * @var         string $version         Mautic version
 * @link        https://github.com/virgilwashere/mautic-cron-commands
 * @link        https://mautic.org
 * @link        https://mauteam.org/mautic/mautic-admins/mautic-cron-jobs-for-dummies-marketers-too/
 * @see         https://www.mautic.org/docs/en/setup/cron_jobs.html
 * @see         https://www.mautic.org/docs/en/tips/update-failed.html
 * @see         https://gist.github.com/escopecz/9a1a0b10861941a457f4
 * @filesource  commands.php
 *
 */

$server_name = filter_input(INPUT_SERVER, 'SERVER_NAME');
if (isset($_SERVER['MAUTIC_ROOT'])) {
    // The path to Mautic root.
    // Please note: %kernel.root_dir% = $docroot/app

    // $docroot = filter_input(INPUT_SERVER, 'MAUTIC_ROOT').'/mautic';
    $docroot = filter_input(INPUT_SERVER, 'MAUTIC_ROOT');
} else {
    $docroot = __DIR__;
}

require_once $docroot.'/autoload.php';
require_once $docroot.'/app/AppKernel.php';
require $docroot.'/vendor/autoload.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;

// IMPORTANT - Change Secret phrase on each install
$secretphrase= "pyogyrmKhqJtkThEsE7";

if (!isset($_GET[$secretphrase])) {
    http_response_code(401);
    die('Unauthorized');
}
defined('IN_MAUTIC_CONSOLE') or define('IN_MAUTIC_CONSOLE', 1);

$version = file_get_contents($docroot.'/app/version.txt');
if (isset($_GET['pretty'])) {
    $pretty = $_GET['pretty'];
}
$request_uri =  "//{$server_name}{$_SERVER['REQUEST_URI']}";

$allowedCmds = array(
    'list',
    'mautic:segments:update',

    'mautic:campaigns:update',
    'mautic:campaigns:trigger',
    'cache:clear',
    'mautic:emails:send',
    'mautic:emails:fetch',
    'mautic:emails:send --quiet',
    'mautic:emails:fetch --quiet',
    'mautic:broadcasts:send',
    'mautic:broadcasts:send --quiet',
    'mautic:broadcasts:send --channel=email',
    'mautic:broadcasts:send --channel=sms',
    'mautic:messages:send',
    'mautic:campaigns:messages',
    'mautic:campaigns:messages --channel=email',
    'mautic:campaigns:messages --channel=sms',
    'mautic:queue:process',
    'mautic:webhooks:process',
    'mautic:reports:scheduler',
    'mautic:plugins:update',
    'mautic:iplookup:download',
    'mautic:assets:generate',
    'mautic:segments:update --force',
    'mautic:campaigns:update --force',
    'mautic:campaigns:trigger --force',
    'mautic:segments:update',
    'mautic:segments:update --force',
    'mautic:segments:update --max-contacts=300 --batch-limit=300',
    'mautic:segments:update --max-contacts=300 --batch-limit=300 --quiet',
    'mautic:segments:update --max-contacts=300 --batch-limit=300 --force',
    'mautic:segments:update --max-contacts=1000 --batch-limit=1000',
    'mautic:segments:update --max-contacts=1000 --batch-limit=1000 --quiet',
    'mautic:campaigns:update --max-contacts=100 --quiet',
    'mautic:campaigns:update --max-contacts=300 --quiet',
    'mautic:campaigns:trigger --quiet',
    'cache:clear --no-interaction --no-warmup --no-optional-warmers',
    'cache:warmup --no-interaction --no-optional-warmers',
    'mautic:social:monitoring',
    'mautic:integration:pushleadactivity --integration=XXX',
    'mautic:integration:fetchleads --integration=XXX',
	
    'mautic:integration:fetchleads -i Salesforce',

    'mautic:integration:fetchleads -i Salesforce -h',
    'mautic:integration:fetchleads -i Salesforce --fetch-all',
    'mautic:integration:fetchleads -i Salesforce --start-date=2021-06-20 --end-date=2021-06-23',

	'mautic:integration:synccontacts -i Salesforce -h',
	'mautic:integration:pushactivity -i Salesforce -h',
	
	'mautic:integration:synccontacts --integration=Salesforce',
	'mautic:integration:pushactivity --integration=Salesforce',
	'mautic:integration:synccontacts --integration=Salesforce --start-date=2021-06-20 --end-date=2021-06-23',
	'mautic:integration:synccontacts --integration=Salesforce --time-interval=-30 minutes',
	'mautic:integration:synccontacts --integration=Salesforce --time-interval=-30 minutes',
	'mautic:integration:synccontacts --integration=Salesforce --time-interval=30 minutes',
	'mautic:integration:synccontacts --integration=Salesforce --time-interval=30 minutes',
	'mautic:integration:pushactivity -i Salesforce ',
	'mautic:integration:synccontacts --integration=Salesforce --time-interval=1 day',
	'mautic:integration:pushactivity --integration=Salesforce --time-interval=1 day',
	'mautic:integration:synccontacts --integration=Salesforce --time-interval=29 days',
	'mautic:integration:pushactivity --integration=Salesforce --time-interval=29 days',
	
    'mautic:integration:fetchleads -i Sugarcrm --fetch-all',
    'mautic:integration:fetchleads -i Sugarcrm --time-interval=20minutes',
    'mautic:integration:pushactivity -i Sugarcrm',
    'mautic:integration:pushleadactivity -i Sugarcrm',
    'mautic:integration:synccontacts -i Sugarcrm',

    'SugarcrmIntegration::getLeads',
    'SugarcrmIntegration::getLeads',
    'SugarcrmIntegration::getCompanies',
    'SugarcrmIntegration::pushLeads',
    'SugarcrmIntegration::pushCompanies',
	
    'mautic:integration:fetchleads --integration=Hubspot',
    'mautic:import',
    'mautic:import --limit=600',
    'mautic:import --limit=600 --quiet',
    'mautic:dnc:import --limit=600',
    'mautic:dnc:import --limit=600 --quiet',
    'mautic:maintenance:cleanup --no-interaction --days-old=90 --dry-run',
    'mautic:maintenance:cleanup --no-interaction --days-old=365 --dry-run',
    'mautic:maintenance:cleanup --no-interaction --days-old=90',
    'mautic:maintenance:cleanup --no-interaction --days-old=365',
    'doctrine:migrations:status',
    'doctrine:migrations:status --show-versions',
    'doctrine:migrations:migrate --allow-no-migration --dry-run',
    'doctrine:migrations:migrate --allow-no-migration --no-interaction',
    'doctrine:migrations:migrate --allow-no-migration --query-time --dry-run',
    'doctrine:migrations:migrate --allow-no-migration --query-time --no-interaction',
    'doctrine:schema:update',
    'doctrine:schema:update --dump-sql',
    'doctrine:schema:validate',
    'doctrine:schema:update --no-interaction --dump-sql --force',
    'doctrine:schema:update --no-interaction --force',
    'debug:swiftmailer',
    'debug:router',
    'doctrine:mapping:info',
    'debug:event-dispatcher',
    'mautic:install:data --no-interaction --force',
    'mautic:contacts:deduplicate',
    'mautic:unusedip:delete',
    'mautic:dashboard:warm',
    'mautic:campaign:summarize',
    'mautic:update:find',
    'mautic:update:apply --no-interaction --force',
	'',
		'mautic:segment:update -i 1',
		'mautic:segment:update -i 2',
		'mautic:segment:update -i 3',
		'mautic:segment:update -i 4',
		'mautic:segment:update -i 5',
		'mautic:segment:update -i 6',
		'mautic:segment:update -i 7',
		'mautic:segment:update -i 8',
		'mautic:segment:update -i 9',
		'mautic:segment:update -i 10',
		'mautic:segment:update -i 11',
		'mautic:segment:update -i 12',
		'mautic:segment:update -i 13',
		'mautic:segment:update -i 14',
		'mautic:segment:update -i 15',
		'mautic:segment:update -i 16',
		'mautic:segment:update -i 17',
		'mautic:segment:update -i 18',
		'mautic:segment:update -i 19',
		'mautic:segment:update -i 20',
		'mautic:segment:update -i 21',
		'mautic:segment:update -i 22',
		'mautic:segment:update -i 23',
		'mautic:segment:update -i 24',
		'mautic:segment:update -i 25',
		'mautic:segment:update -i 26',
		'mautic:segment:update -i 27',
		'mautic:segment:update -i 28',
		'mautic:segment:update -i 29',
		'mautic:segment:update -i 30',
		'mautic:segment:update -i 31',
		'mautic:segment:update -i 32',
		'mautic:segment:update -i 33',
		'mautic:segment:update -i 34',
		'mautic:segment:update -i 35',
		'mautic:segment:update -i 36',
		'mautic:segment:update -i 37',
		'mautic:segment:update -i 38',
		'mautic:segment:update -i 39',
		'mautic:segment:update -i 40',
		'mautic:segment:update -i 41',
		'mautic:segment:update -i 42',
		'mautic:segment:update -i 43',
		'mautic:segment:update -i 44',
		'mautic:segment:update -i 45',
		'mautic:segment:update -i 46',
		'mautic:segment:update -i 47',
		'mautic:segment:update -i 48',
		'mautic:segment:update -i 49',
		'mautic:segment:update -i 50',
		'mautic:segment:update -i 51',
		'mautic:segment:update -i 52',
		'mautic:segment:update -i 53',
		'mautic:segment:update -i 54',
		'mautic:segment:update -i 55',
		'mautic:segment:update -i 56',
		'mautic:segment:update -i 57',
		'mautic:segment:update -i 58',
		'mautic:segment:update -i 59',
		'mautic:segment:update -i 60',
		'mautic:segment:update -i 61',
		'mautic:segment:update -i 62',
		'mautic:segment:update -i 63',
		'mautic:segment:update -i 64',
		'mautic:segment:update -i 65',
		'mautic:segment:update -i 66',
		'mautic:segment:update -i 67',
		'mautic:segment:update -i 68',
		'mautic:segment:update -i 69',
		'mautic:segment:update -i 70',
		'mautic:segment:update -i 71',
		'mautic:segment:update -i 72',
		'mautic:segment:update -i 73',
		'mautic:segment:update -i 74',
		'mautic:segment:update -i 75',
		'mautic:segment:update -i 76',
		'mautic:segment:update -i 77',
		'mautic:segment:update -i 78',
		'mautic:segment:update -i 79',
		'mautic:segment:update -i 80',
		'mautic:segment:update -i 81',
		'mautic:segment:update -i 82',
		'mautic:segment:update -i 83',
		'mautic:segment:update -i 84',
		'mautic:segment:update -i 85',
		'mautic:segment:update -i 86',
		'mautic:segment:update -i 87',
		'mautic:segment:update -i 88',
		'mautic:segment:update -i 89',
		'mautic:segment:update -i 90',
		'mautic:segment:update -i 91',
		'mautic:segment:update -i 92',
		'mautic:segment:update -i 93',
		'mautic:segment:update -i 94',
		'mautic:segment:update -i 95',
		'mautic:segment:update -i 96',
		'mautic:segment:update -i 97',
		'mautic:segment:update -i 98',
		'mautic:segment:update -i 99',
		'mautic:segment:update -i 100',
'',
		'mautic:campaigns:update -i 1',
		'mautic:campaigns:update -i 2',
		'mautic:campaigns:update -i 3',
		'mautic:campaigns:update -i 4',
		'mautic:campaigns:update -i 5',
		'mautic:campaigns:update -i 6',
		'mautic:campaigns:update -i 7',
		'mautic:campaigns:update -i 8',
		'mautic:campaigns:update -i 9',
		'mautic:campaigns:update -i 10',
		'mautic:campaigns:update -i 11',
		'mautic:campaigns:update -i 12',
		'mautic:campaigns:update -i 13',
		'mautic:campaigns:update -i 14',
		'mautic:campaigns:update -i 15',
		'mautic:campaigns:update -i 16',
		'mautic:campaigns:update -i 17',
		'mautic:campaigns:update -i 18',
		'mautic:campaigns:update -i 19',
		'mautic:campaigns:update -i 20',
		'mautic:campaigns:update -i 21',
		'mautic:campaigns:update -i 22',
		'mautic:campaigns:update -i 23',
		'mautic:campaigns:update -i 24',
		'mautic:campaigns:update -i 25',
		'mautic:campaigns:update -i 26',
		'mautic:campaigns:update -i 27',
		'mautic:campaigns:update -i 28',
		'mautic:campaigns:update -i 29',
		'mautic:campaigns:update -i 30',
		'mautic:campaigns:update -i 31',
		'mautic:campaigns:update -i 32',
		'mautic:campaigns:update -i 33',
		'mautic:campaigns:update -i 34',
		'mautic:campaigns:update -i 35',
		'mautic:campaigns:update -i 36',
		'mautic:campaigns:update -i 37',
		'mautic:campaigns:update -i 38',
		'mautic:campaigns:update -i 39',
		'mautic:campaigns:update -i 40',
		'mautic:campaigns:update -i 41',
		'mautic:campaigns:update -i 42',
		'mautic:campaigns:update -i 43',
		'mautic:campaigns:update -i 44',
		'mautic:campaigns:update -i 45',
		'mautic:campaigns:update -i 46',
		'mautic:campaigns:update -i 47',
		'mautic:campaigns:update -i 48',
		'mautic:campaigns:update -i 49',
		'mautic:campaigns:update -i 50',
		'mautic:campaigns:update -i 51',
		'mautic:campaigns:update -i 52',
		'mautic:campaigns:update -i 53',
		'mautic:campaigns:update -i 54',
		'mautic:campaigns:update -i 55',
		'mautic:campaigns:update -i 56',
		'mautic:campaigns:update -i 57',
		'mautic:campaigns:update -i 58',
		'mautic:campaigns:update -i 59',
		'mautic:campaigns:update -i 60',
		'mautic:campaigns:update -i 61',
		'mautic:campaigns:update -i 62',
		'mautic:campaigns:update -i 63',
		'mautic:campaigns:update -i 64',
		'mautic:campaigns:update -i 65',
		'mautic:campaigns:update -i 66',
		'mautic:campaigns:update -i 67',
		'mautic:campaigns:update -i 68',
		'mautic:campaigns:update -i 69',
		'mautic:campaigns:update -i 70',
		'mautic:campaigns:update -i 71',
		'mautic:campaigns:update -i 72',
		'mautic:campaigns:update -i 73',
		'mautic:campaigns:update -i 74',
		'mautic:campaigns:update -i 75',
		'mautic:campaigns:update -i 76',
		'mautic:campaigns:update -i 77',
		'mautic:campaigns:update -i 78',
		'mautic:campaigns:update -i 79',
		'mautic:campaigns:update -i 80',
		'mautic:campaigns:update -i 81',
		'mautic:campaigns:update -i 82',
		'mautic:campaigns:update -i 83',
		'mautic:campaigns:update -i 84',
		'mautic:campaigns:update -i 85',
		'mautic:campaigns:update -i 86',
		'mautic:campaigns:update -i 87',
		'mautic:campaigns:update -i 88',
		'mautic:campaigns:update -i 89',
		'mautic:campaigns:update -i 90',
		'mautic:campaigns:update -i 91',
		'mautic:campaigns:update -i 92',
		'mautic:campaigns:update -i 93',
		'mautic:campaigns:update -i 94',
		'mautic:campaigns:update -i 95',
		'mautic:campaigns:update -i 96',
		'mautic:campaigns:update -i 97',
		'mautic:campaigns:update -i 98',
		'mautic:campaigns:update -i 99',
		'mautic:campaigns:update -i 100',
'',
		'mautic:campaigns:trigger -i 1',
		'mautic:campaigns:trigger -i 2',
		'mautic:campaigns:trigger -i 3',
		'mautic:campaigns:trigger -i 4',
		'mautic:campaigns:trigger -i 5',
		'mautic:campaigns:trigger -i 6',
		'mautic:campaigns:trigger -i 7',
		'mautic:campaigns:trigger -i 8',
		'mautic:campaigns:trigger -i 9',
		'mautic:campaigns:trigger -i 10',
		'mautic:campaigns:trigger -i 11',
		'mautic:campaigns:trigger -i 12',
		'mautic:campaigns:trigger -i 13',
		'mautic:campaigns:trigger -i 14',
		'mautic:campaigns:trigger -i 15',
		'mautic:campaigns:trigger -i 16',
		'mautic:campaigns:trigger -i 17',
		'mautic:campaigns:trigger -i 18',
		'mautic:campaigns:trigger -i 19',
		'mautic:campaigns:trigger -i 20',
		'mautic:campaigns:trigger -i 21',
		'mautic:campaigns:trigger -i 22',
		'mautic:campaigns:trigger -i 23',
		'mautic:campaigns:trigger -i 24',
		'mautic:campaigns:trigger -i 25',
		'mautic:campaigns:trigger -i 26',
		'mautic:campaigns:trigger -i 27',
		'mautic:campaigns:trigger -i 28',
		'mautic:campaigns:trigger -i 29',
		'mautic:campaigns:trigger -i 30',
		'mautic:campaigns:trigger -i 31',
		'mautic:campaigns:trigger -i 32',
		'mautic:campaigns:trigger -i 33',
		'mautic:campaigns:trigger -i 34',
		'mautic:campaigns:trigger -i 35',
		'mautic:campaigns:trigger -i 36',
		'mautic:campaigns:trigger -i 37',
		'mautic:campaigns:trigger -i 38',
		'mautic:campaigns:trigger -i 39',
		'mautic:campaigns:trigger -i 40',
		'mautic:campaigns:trigger -i 41',
		'mautic:campaigns:trigger -i 42',
		'mautic:campaigns:trigger -i 43',
		'mautic:campaigns:trigger -i 44',
		'mautic:campaigns:trigger -i 45',
		'mautic:campaigns:trigger -i 46',
		'mautic:campaigns:trigger -i 47',
		'mautic:campaigns:trigger -i 48',
		'mautic:campaigns:trigger -i 49',
		'mautic:campaigns:trigger -i 50',
		'mautic:campaigns:trigger -i 51',
		'mautic:campaigns:trigger -i 52',
		'mautic:campaigns:trigger -i 53',
		'mautic:campaigns:trigger -i 54',
		'mautic:campaigns:trigger -i 55',
		'mautic:campaigns:trigger -i 56',
		'mautic:campaigns:trigger -i 57',
		'mautic:campaigns:trigger -i 58',
		'mautic:campaigns:trigger -i 59',
		'mautic:campaigns:trigger -i 60',
		'mautic:campaigns:trigger -i 61',
		'mautic:campaigns:trigger -i 62',
		'mautic:campaigns:trigger -i 63',
		'mautic:campaigns:trigger -i 64',
		'mautic:campaigns:trigger -i 65',
		'mautic:campaigns:trigger -i 66',
		'mautic:campaigns:trigger -i 67',
		'mautic:campaigns:trigger -i 68',
		'mautic:campaigns:trigger -i 69',
		'mautic:campaigns:trigger -i 70',
		'mautic:campaigns:trigger -i 71',
		'mautic:campaigns:trigger -i 72',
		'mautic:campaigns:trigger -i 73',
		'mautic:campaigns:trigger -i 74',
		'mautic:campaigns:trigger -i 75',
		'mautic:campaigns:trigger -i 76',
		'mautic:campaigns:trigger -i 77',
		'mautic:campaigns:trigger -i 78',
		'mautic:campaigns:trigger -i 79',
		'mautic:campaigns:trigger -i 80',
		'mautic:campaigns:trigger -i 81',
		'mautic:campaigns:trigger -i 82',
		'mautic:campaigns:trigger -i 83',
		'mautic:campaigns:trigger -i 84',
		'mautic:campaigns:trigger -i 85',
		'mautic:campaigns:trigger -i 86',
		'mautic:campaigns:trigger -i 87',
		'mautic:campaigns:trigger -i 88',
		'mautic:campaigns:trigger -i 89',
		'mautic:campaigns:trigger -i 90',
		'mautic:campaigns:trigger -i 91',
		'mautic:campaigns:trigger -i 92',
		'mautic:campaigns:trigger -i 93',
		'mautic:campaigns:trigger -i 94',
		'mautic:campaigns:trigger -i 95',
		'mautic:campaigns:trigger -i 96',
		'mautic:campaigns:trigger -i 97',
		'mautic:campaigns:trigger -i 98',
		'mautic:campaigns:trigger -i 99',
		'mautic:campaigns:trigger -i 100',

);

// color:#FCB833;
$css='    <style type="text/css">
        .black   { color: #111111 }
        .gray    { color: #AAAAAA }
        .silver  { color: #DDDDDD }
        .white   { color: #FFFFFF }
        .aqua    { color: #7FDBFF }
        .blue    { color: #0074D9 }
        .navy    { color: #001F3F }
        .teal    { color: #39CCCC }
        .green   { color: #2ECC40 }
        .olive   { color: #3D9970 }
        .lime    { color: #01FF70 }
        .yellow  { color: #FFDC00 }
        .orange  { color: #FF851B }
        .red     { color: #FF4136 }
        .fuchsia { color: #F012BE }
        .purple  { color: #B10DC9 }
        .maroon  { color: #85144B }
        a {
            transition: color .4s;
            color: #265C83;
        }
        a:link,
        a:visited { color: #265C83; }
        a:hover   { color: #7FDBFF; }
        a:active  {
            transition: color .3s;
            color: #0074D9;
        }
        .link { text-decoration: none; }
        body {
            padding: 20px;
            font-family: "Lucida Grande", "Lucida Sans Unicode", "Lucida Sans", Geneva, Arial, sans-serif;
        }
        li { font-size: smaller; }
        h3 {
            font-family: Open Sans, Helvetica, Arial,sans-serif;
        }
        .container {
            padding: 0px 20px 0px;
            max-width: 600px;
            background-color:#ffffff;
            border: 3px solid #ffffff;
        }
        .container__logo {
            display: inline-block;
            vertical-align: top;
            margin: 20px 20px 0 0;
            width: 25%;
            border: 3px solid #ffffff;
        }
        .container__heading {
            display: inline-block;
            vertical-align: top;
            text-align: center;
            width: 45%;
            color:#000;
            border: 3px solid #ffffff;
        }
        .container__image {
            display: inline-block;
            vertical-align: top;
            margin: 5px 10px 5px 0;
            width: 13%;
            border: 3px solid #ffffff;
        }
        .container__results {
            display: inline-block;
            vertical-align: top;
            width: 80%;
            color:#000000;
            border: 3px solid #ffffff;
        }
        .container__arrow {
            display: inline-block;
            vertical-align: top;
            padding: 0px 10px 0px 0px;
            text-align:center;
            max-width: 160px;
            background-color:#ffffff;
            border: 3px solid #ffffff;
        }
        @media (max-width: 620px) {
            .container__results {
                width: 100%;
            }
        }
		
		   div#content {
   display: none;
  }

div#loading {
   top: 200 px;
  margin: auto;
  position: absolute;
  z-index: 1000;
  width: 160px;
  height: 24px;
  background: url(img/142.gif) no-repeat;
  cursor: wait;
 }
 
    </style>';
$html_meta = '    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="author" content="'. $author .'">
    <meta name="description" content="Mautic cron and maintenance commands">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow" />
    <link rel="shortcut icon" href="/media/images/favicon.ico">
    <link rel="icon" type="image/x-icon" href="/media/images/favicon.ico" />
    <link rel="icon" sizes="72x72" href="/media/images/favicon.ico">
    <link rel="apple-touch-icon" href="/media/images/apple-touch-icon.png" />';

// If you want to use an inline image, add the base64 encoded image
// here, and uncomment the relevant parameter.

// $mautibot_base64 = 'iVBORw0KG...';
// $mautibot='"data:image/png;base64, '.$mautibot_base64.'"';
// $logo_base64 = 'iVBORw0KGgoA...';
// $logo='"data:image/png;base64, '.$logo_base64.'"';
$docroot = filter_input(INPUT_SERVER, 'MAUTIC_ROOT');
$cdn='https://cdn.jsdelivr.net/gh/virgilwashere/mautic-cron-commands';
$mautibot=$docroot . '/media/images/login_logo.png';
$logo=$docroot . '/media/images/login_logo.png';
$backarrow=$cdn . '/assets/arrow-left-trans.png';

if (!isset($_GET['task'])) {

    // Command selection ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Mautic Maintenance Commands</title>
<?php if (isset($pretty)) {
        echo "$html_meta\n";
        echo "$css\n"; ?>
</head><body>
    <div class="container">
        <a target="_blank" href="/s/login"><img class="container__logo" src="<?php echo $logo ?>" alt="logo"></a>

    </div>
		<div class="container__heading">
            <h3><?php echo $server_name ?></h3><h3>maintenance commands</h3>
        </div>
    </div>
<?php } else { ?>
</head><body>
    <h3><?php echo $server_name ?> maintenance commands</h3>

<?php }

    if (isset($version)) { ?>
        <p title="version"><small>Mautic version: <strong style="color:blue"><?php echo $version ?></strong></small></p>

<?php } ?>
    <p title="cmdlist">Select a command from the list:</p>
    <ul id="allowedcommandslist">
<?php
    foreach ($allowedCmds as $task) {
        $link = $request_uri.'&task='.urlencode($task);
        echo '<li><a href="'.$link.'">'.$task."</a></li>\n";
    } ?>
    </ul><hr>
    <p title="warning">Please, <strong style="color:red">backup your database</strong> before executing <code>doctrine:*:*</code> commands! (or anything with <code>--force</code>)</p>
    <p><a target="_blank" href="https://www.mautic.org/docs">Mautic documentation</a>: <a href="https://www.mautic.org/docs/en/setup/cron_jobs.html" target="_blank">Setup cronjobs</a> and <a href="https://www.mautic.org/docs/en/tips/update-failed.html" target="_blank">Upgrade troubleshooting</a>.</p>
</body></html>
<?php
    die;
}

$task = urldecode($_GET['task']);
if (!in_array($task, $allowedCmds)) {
    http_response_code(403);
    die("Command {$task} is not allowed!");
}

// if (isset($pretty)) {
//     // $options = ' --ansi';
//     $options = '';
// } else {
    $options = '';
// }
$fullCommand = explode(' ', $task);
$command = $fullCommand[0];
$argsCount = count($fullCommand) - 1;
$console = array('console'.$options, $command);
if ($argsCount) {
    for ($i = 1; $i <= $argsCount; $i++) {
        $console[] = $fullCommand[$i];
    }
}

// Command results ?>
<?php if (isset($pretty)) { ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Command: <?php echo implode(' ', $console) ?></title>
    <?php echo "$html_meta\n" ?>
    <?php echo "$css\n" ?>
</head><body>
    <div class="container">
        <img class="container__image" src="<?php echo $mautibot ?>" alt="Mautibot™">
        <div class="container__results">
            <h3><?php echo $server_name ?> command output</h3>
            <p>Executing <code><?php echo implode(' ', $console) ?></code></p>
        </div>
    </div>
<?php } else { ?>
<?php echo $server_name ?> command output<br />
Executing <code><?php echo implode(' ', $console) ?></code><br />
<?php }

// Run the application

try {
    $input  = new ArgvInput($console);
    $output = new BufferedOutput();
    $kernel = new AppKernel('prod', false);
    $app    = new Application($kernel);
    $app->setAutoExit(false);
    $result = $app->run($input, $output);

    // command output
    echo "\n<pre>\n{$output->fetch()}</pre>\n";

} catch (\Exception $e) {
    echo "\nException raised: {$e->getMessage()}\n";

} finally {
    if (isset($pretty)) { ?>
    <div class="container">
        <a href="javascript:history.back(1)" title="Return to the previous page">Return to the previous page
            <img class="container__arrow" src="<?php echo $backarrow ?>" alt="&laquo; Go back">
        </a>
    </div>
</body></html>

<?php }
}
