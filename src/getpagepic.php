<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 3/1/2019
 * Time: 12:21 AM
 */

require __DIR__ .'/../vendor/autoload.php';
require_once 'resources/secrets.php';

$data = array(
//    0 => array('261745427847218', 'A random texture from minecraft until all have been posted and then i die'),
//    1 => array('327614957883057', 'A random texture from terraria until all have been posted and then i die'),
    2 => array('382404089204851', 'Actual Fact bot 5338'),
//    3 => array('1051715078334151', 'AlbumBot 808'),
//    4 => array('1801596149950905', 'ArtPostbot1519'),
//    5 => array('380940812265973', 'BabelBot 5000'),
//    6 => array('397068630827194', 'BartenderBot 1862'),
//    7 => array('234738744057447', 'BASR-FM 168.1'),
//    8 => array('677994329268956', 'Ben Garrison Bot 1980'),
//    9 => array('2017448901882151', 'BiblepostBot 1111'),
//    10 => array('1061440390696615', 'Botbot 1000'),
//    11 => array('946056605583121', 'BotoB 8008'),
//    12 => array('480581822302999', 'BottomText 5000'),
//    13 => array('471237779901473', 'BottomText 5000 v2.0'),
//    14 => array('461541427691148', 'BurgerBot 4545'),
//    15 => array('227202038206959', 'CensorBot 1111'),
//    16 => array('597049390730350', 'ChefBot1949'),
//    17 => array('543629216138646', 'ChristianMom Bot 1542'),
//    18 => array('327272867661400', 'Colormachine'),
//    19 => array('642008279564413', 'Content Aware Bot'),
//    20 => array('601244333633306', 'CraftingBot 64'),
//    21 => array('680457985653773', 'CyanideBot69'),
//    22 => array('273342366696518', 'DeviantBot 7245'),
//    23 => array('604850663246586', 'DictionaryPostbot'),
//    24 => array('448767758992760', 'DogePost Bot 1619'),
//    25 => array('1648973728734532', 'DogpostBot'),
//    26 => array('2160357754229015', 'ElementBot 9654'),
//    27 => array('301559447292433', 'EmojiBot 101'),
//    28 => array('389038564976223', 'FaceswapBot 6656'),
//    29 => array('2030280410420562', 'Face Generation Bot 1955'),
    30 => array('1873137272813735', 'FactpostBot4286'),
//    31 => array('1411977962202193', 'FunnyPost Bot 5000'),
//    32 => array('1245774332231777', 'Garkov PostBot 1978'),
//    33 => array('481609425672744', 'GreentextBot'),
//    34 => array('230522160975862', 'HoroscopeBot 12'),
//    35 => array('126951917963098', 'HungerGamesBot 74'),
//    36 => array('227522548151200', 'IdeaBot 5200'),
//    37 => array('1687844851315186', 'ImposterBot Pmc2963468'),
//    38 => array('1041852045984002', 'InspiroBot Quotes'),
//    39 => array('456497227863361', 'InspiroBot.me'),
//    40 => array('351013602296706', 'Jewishnamebot5779'),
    41 => array('281788746015448', 'JokeBot7490'),
//    42 => array('532420467194959', 'Local Forecast Bot'),
//    43 => array('438060519936444', 'MathsBot 271828'),
//    44 => array('511676689354000', 'MDHHCD-Bot'),
//    45 => array('289271555113618', 'MusictakesBot 433'),
//    46 => array('2159392794273314', 'Namebot 1372'),
//    47 => array('836320316758619', 'NothingPostBot 0000'),
//    48 => array('1092094064306230', 'OreoBot 1912'),
//    49 => array('2284646001810470', 'Papa’s Cafeteria Bot Of Chaos'),
//    50 => array('1958354337580600', 'PerlinFiedlBot 4150'),
//    51 => array('230785391035833', 'Pie Chart Bot 45%'),
//    52 => array('619174248513666', 'Ratherbot 1111'),
//    53 => array('320491941874939', 'ReviewBrahBot'),
//    54 => array('616526432141419', 'RosesAreRedBot 4823'),
//    55 => array('2250876378571452', 'Rogue-likebot 1980'),
//    56 => array('2326440757368440', 'Russian Roulette Bot 1667'),
//    57 => array('2309494099083589', 'SelfieBot9004'),
//    58 => array('197820040580620', 'SentiencePostBot 5000'),
//    59 => array('1663308127217572', 'ShitpostBot 5000'),
//    60 => array('1671572529621187', 'StandBot4444'),
//    61 => array('190160088186213', 'StreetViewBot 5000 v2.0'),
//    62 => array('2169855356565545', 'Styletransferbot9683'),
//    63 => array('392044161622515', 'SztukapostBot2044'),
//    64 => array('1091874840975747', 'Text2SpeechBot 0010'),
//    65 => array('757632331272960', 'Thanos Bot Thanos Bot'),
//    66 => array('2161758867228069', 'Thispersondoesnotexist'),
//    67 => array('2013364938903240', 'ToolpostBot'),
//    68 => array('782263932109845', 'TorrentBot 1337'),
//    69 => array('169926290587803', 'TrackMania Trackpostbot 2004'),
//    70 => array('361707421327012', 'US Election Bot 1776'),
//    71 => array('626406971129420', 'VennDiagram Bot 1111'),
//    72 => array('992214964320317', 'VICEpostbot'),
//    73 => array('565819893850538', 'Vidya Game Bot 1337'),
//    74 => array('1297440277030625', 'VortessenceBot 2.0'),
//    75 => array('581937198909172', 'VsauceBot Here'),
//    76 => array('324745798153232', 'Waifu Generation Bot 1964'),
//    77 => array('630018863852338', 'WeatherBot 5000'),
//    78 => array('288845988198838', 'WikipostBot 5000'),
//    79 => array('1044088892269897', 'RPB: Posting Bot'),
//    80 => array('650531808477945', 'CowManglerBot 5000'),
//    81 => array('192843011139923', 'DeathgripsBot 5000'),
//    82 => array('812952008905469', 'JermaBot 5985'),
//    83 => array('177214122755874', 'JojoPostBot 5000'),
//    84 => array('705121386323292', 'PerhapsBot 5000'),
//    85 => array('890595927692782', 'Shitposthony Botano'),
//    86 => array('168882356889886', 'SpongepostBob 5000'),
//    87 => array('1890487364511068', 'TestpostBot 5000'),
//    88 => array('140196830042531', 'Weeabot 5000'),
//    89 => array('156961951521995', 'BezierPostBot 0000'),
    90 => array('376017136540636', 'BuzzfeedQuiz Bot 2006'),
//    91 => array('606599813006727', 'ChessBot 1951'),
//    92 => array('725338660959565', 'ClickBot 2000'),
//    93 => array('2181940238716979', 'CommentBot 2005'),
//    94 => array('500738470399816', 'CountryBot 0208'),
//    95 => array('303209593793454', 'Creepypasta bot'),
//    96 => array('1693122380765924', 'DadsGoοgləHistoryBot 3000'),
//    97 => array('167522694156709', 'Diseasepostbot 1665'),
//    98 => array('382556028901388', 'ElektronischeBot8888'),
//    99 => array('1682505085195596', 'Encarta95PostBot'),
//    100 => array('242003913139053', 'FlameFoldBot 303'),
//    101 => array('336364413873965', 'Flexbot 2954'),
//    102 => array('566460700486993', 'FMKbot'),
//    103 => array('917733128429255', 'Fractalbot050'),
//    104 => array('519843665128312', 'Good Idea Bot'),
//    105 => array('1932305243552494', 'HankBot0419307'),
//    106 => array('601168763662818', 'JimmyBot7872'),
//    107 => array('494664004295264', 'LossBot 12250'),
//    108 => array('556727934829849', 'MillennialBot 2019'),
//    109 => array('343081899542327', 'PaintBot'),
//    110 => array('2199316086763975', 'PaintBot 2.0'),
//    111 => array('274971683143606', 'PajeetBot 2021'),
//    112 => array('1935936326459955', 'PHCommentBot'),
//    113 => array('112840526053679', 'REDACTEDHowBot 5000'),
//    114 => array('388782878354249', 'Shirtpostbot 2300'),
//    115 => array('549416478854758', 'SpilledInk Bot 42'),
//    116 => array('512340822260257', 'TextpostBot 98'),
//    117 => array('436607433533784', 'QuoteshitBot'),
//    118 => array('335729730128446', 'WordpostBot'),
);

$FB_helper = new FakepostBot\FacebookHelper();
$fb = $FB_helper->init($_APP_ID, $_APP_SECRET, $_ACCESS_TOKEN_DEBUG);
$dt = new FakepostBot\DataLogger();

foreach ($data as $entry) {
    $page_id = $entry[0];
    $bot = $entry[1];

    $dt->logdata($bot);

    $message = 'getting bot pic.';
    $dt->logdata($message);

    try {
        $pic_url = $FB_helper->getPicture($fb, $page_id);
        $IMAGE_PATH_NEW = 'C:\Users\Diego\PhpstormProjects\FakePostBot\tests\bot images\\'.$bot.'.png';

        $ImgFetcher = new FakepostBot\ImageFetcher();
        $isSuccess = $ImgFetcher->saveImageLocally($pic_url, $IMAGE_PATH_NEW, true);
        if (!$isSuccess) {
            $message = $bot.' failed.';
            $dt->logdata($message);
        }
    } catch (Exception $e) {
        $message = $bot.' failed bc exception.';
        $dt->logdata($message);
    }
}
