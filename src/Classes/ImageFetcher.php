<?php
/**
 * Created by PhpStorm.
 * User: Diego
 * Date: 1/14/2019
 * Time: 9:45 PM
 */

namespace FakepostBot;

use Intervention\Image\ImageManagerStatic as Image;

class ImageFetcher extends DataLogger
{


    /**
     * @param string $dailydeviations
     * @param string $popular
     * @param array $tags
     * @param array $keywords
     * @return string
     */
    public function buildRSSURL($dailydeviations, $popular, $tags, $keywords)
    {

        $url_rss = 'http://backend.deviantart.com/rss.xml?&q=';

        if ($dailydeviations) {
            // Daily deviations
            $url_rss .= 'special:dd'.rawurlencode(' ');
        } elseif ($popular) {
            // Popular from last 24 hours
            $url_rss .= 'boost:popular'.rawurlencode(' max_age:24h ');
        } else {
            // Any
            $url_rss .= 'meta:all'.rawurlencode(' ');
        }

        $params = '';
        //TODO add searching by title
        foreach ($keywords as $keyword) {
            $params .= $keyword.' ';
        }

        foreach ($tags as $tag) {
            $params .= 'tag:'.$tag.' ';
        }

        // Exclude literature category since most are just text
        // Not compatible with meta:all tag
        if ($dailydeviations || $popular) {
            $lit = '-in:literature ';
            $url_rss .= rawurlencode($lit);
        }


        if (!$popular) {
            $params .= 'sort:time ';
        }

        $url_rss .= rawurlencode($params).'&=';

        // logging
        $message = 'fetching [' . $url_rss . ']';
        $this->logdata($message);

        return $url_rss;
    }


    /**
     * @desc GET request to DeviantArt servers
     * @param string $url
     * @param string $media
     * @return string
     */
    public function getRawDeviantArtData($url, $media = 'JSON')
    {

        $curl = curl_init();

        //    Internet Explorer 6 on Windows XP SP2
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_ENCODING, "");
        curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($curl, CURLOPT_POSTFIELDS, "");
        curl_setopt($curl, CURLOPT_USERAGENT, $agent);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = $media.' cURL Error #:' . $err.'  url: '.$url.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  $media.' Http code error #:' . $httpcode.'  url: '.$url.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            return $response;
        }
        return '';
    }



    /**
     * @param string $type
     * @param array $tags
     * @param array $keywords
     * @return array
     */
    public function getImagelinksFromRSS($type, $tags, $keywords)
    {

        if ($type == 'DAILY') {
            // DailyDeviations
            //http://backend.deviantart.com/rss.xml?q=special:dd sort:time
            $CURLOPT_URL = $this->buildRSSURL(true, false, $tags, $keywords);
        } elseif ($type == 'POPULAR') {
            // Newest popular
            //http://backend.deviantart.com/rss.xml?q=boost:popular max_age:24h sort:time
            $CURLOPT_URL = $this->buildRSSURL(false, true, $tags, $keywords);
        } elseif ($type == 'ANY') {
            // Newest Any
            //http://backend.deviantart.com/rss.xml?q=meta:all sort:time
            $CURLOPT_URL = $this->buildRSSURL(false, false, $tags, $keywords);
        }

        if (!empty($CURLOPT_URL)) {
            $response = $this->getRawDeviantArtData($CURLOPT_URL, 'RSS');
            if (!empty($response)) {
                //Process XML
                try {
                    $this->logxml($type, $response);

                    /** @var  $links array */
                    $links = $this->parseXMLResponse($response);

                    if (!empty($links)) {
                        return $links;
                    }
                } catch (Exception $e) {
                    $message = $e->getMessage();
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                }
            }
        } else {
            $message = '$CURLOPT_URL empty';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
        return [];
    }

    /**
     * @desc parse and access SimpleXMLElement from local XML
     * @param string $response
     * @return array
     */
    public function parseXMLResponse($response)
    {
        $xml = new \SimpleXMLElement($response);
        $links_array = array();
        /** @var $item SimpleXMLElement */
        foreach ($xml->xpath('channel/item') as $item) {
            if (!empty($item->link)) {
                array_push($links_array, (string)$item->link) ;
            } else {
                if (!empty($item)) {
                    $title = $item->title;
                    $message = $title.' has no links';
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message);
                } else {
                    $message = 'weird xml';
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                }
            }
        }
        return $links_array;
    }


    /**
     * @param string $TYPE
     * @param array $tags
     * @param array $keywords
     * @return string
     */
    public function getRandom($TYPE, $tags, $keywords)
    {

        $ImgFetch = new ImageFetcher();
        $links = $ImgFetch->getImagelinksFromRSS($TYPE, $tags, $keywords);

        if (!empty($links)) {
            $random_index = mt_rand(0, count($links) - 1);
            return $links[$random_index];
        } else {
            $message = 'no links found, retrying with no commands';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message);
            return '';
        }
    }

    /**
     * @param string $url
     * @param string $path
     * @param bool $resize
     * @return bool
     */
    public function saveImageLocally($url, $path, $resize = true)
    {

        $curl = curl_init($url);
        $fp = fopen($path, 'wb');

        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
        curl_setopt($curl, CURLOPT_USERAGENT, $agent);

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);
        fclose($fp);

        if ($err) {
            $message = 'SaveImage cURL Error #:' . $err.' url:'.$url.' response:'.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  'SaveImage Http code error #:' . $httpcode.' url:'.$url.' response:'.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }

            // optimum res for facebook
            if ($resize) {
                /** @var \Intervention\Image\Image $img */
                $img = Image::make($path);
                $w = $img->getWidth();
                $h = $img->getHeight();

                if ($w > 1200 || $h > 630) {
                    if ($w > 1200) {
                        $img->resize(1200, null, function ($constraint) {
                            /** @var \Intervention\Image\Constraint $constraint */
                            $constraint->aspectRatio();
                        });
                    } else {
                        $img->resize(null, 630, function ($constraint) {
                            /** @var \Intervention\Image\Constraint $constraint */
                            $constraint->aspectRatio();
                        });
                    }
                }
                $img->save();
                $img->destroy();
            }

            return true;
        }
        return false;
    }

    /**
     */
    public function randomSourceSPB()
    {
        $curl = curl_init();

        $url = 'https://www.shitpostbot.com/api/randsource';

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => "",
            CURLOPT_HTTPHEADER => array(
                'Content-type: application/json',
            )
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  url: '.$url.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  url: '.$url.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            } else {
                //parse json
                $json = json_decode($response, true);
                if (!empty($json)) {
                    try {
                        return $json['sub']['img']['full'];
                    } catch (Exception $e) {
                        return '';
                    }
                }
            }
        }
        return '';
    }


    /**
     * @return array
     */
    public function randomStyle()
    {
        $style_pool = array(
           'thanos_chin.jpg',
           'brick.jpg',
           'fire.jpg',
           'fish.jpg',
           'grass.jpg',
           'ground_beef.jpg',
           'marmol.jpg',
           'painting.jpg',
           'pasta.jpg',
           'nicolas_cage.jpg',
           'doge.jpg',
           'worms.jpg',
           'meat.jpg',
           'bees.jpg',
           'fungi.jpg',
           'crowd.jpg',
           'zebra.jpg',
           'cum.jpg',
           'cobweb.jpg',
           'coffee_beans.jpg',
           'ash_tray.jpg',
           'water.jpg'
        );

        $rnd_index = mt_rand(0, count($style_pool) - 1);
        $filename = $style_pool[$rnd_index];
        $style = substr($filename, 0, -4);
        $style = str_replace('_', ' ', $style);
        $path = 'C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\textures\\'.$filename;
        return [
            'name' => $style,
            'path'  => $path
        ];
    }


    /**
     * @param string $style
     * @param string $source
     * @return string
     */
    public function deepAiCnnmrf($style, $source = '')
    {
        $curl = curl_init();

        $url = 'https://api.deepai.org/api/CNNMRF';

        if (empty($source)) {
            $ImgFetcher = new ImageFetcher();
            $true_url = 'https://www.shitpostbot.com/'. $ImgFetcher->randomSourceSPB();
        } else {
            $true_url = new \CURLFile($source);
        }

        //if got any image from SPB
        if (!empty($true_url)) {
            // upload local texture
            $data = array(
                'content_image' => $true_url,
                'style_image'   => new \CURLFile($style)
            );

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_TIMEOUT => 240, //4mins
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_SAFE_UPLOAD => false,
                CURLOPT_POSTFIELDS => $data,
                CURLOPT_HTTPHEADER => array(
                    "api-key: 0022d160-2e1d-4c8b-a78c-abb83dd9296a",
                    "content-type: multipart/form-data"
                ),
            ));

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $err = curl_error($curl);

            curl_close($curl);

            if ($err) {
                $message = ' cURL Error #:' . $err.'  url: '.$url.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            } else {
                if ($httpcode != '200') {
                    $message =  ' Http code error #:' . $httpcode.' error: '.$err.' url: '.$url.' response: '.$response;
                    $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                } else {
                    $message =  'deepAI CNNMRF response: '.$response;
                    $this->logdata($message);

                    $json = json_decode($response);
                    if (!empty($json)) {
                        // check if returned error
                        if (property_exists($json, 'err')) {
                            $error = $json->err;
                            $message = 'deepAI CNNMRF error '.$error;
                            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
                        } else {
                            $image = $json->output_url;
                            return $image;
                        }
                    }
                }
            }
        } else {
            $message =  'estilo vacio';
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        }
        return '';
    }

    public function localSourceWikiArt()
    {
        $string = file_get_contents('C:\Users\Diego\PhpstormProjects\FakePostBot\src\resources\wikiart.json');
        $json = json_decode($string, true);
        $rnd_index = mt_rand(0, count($json) - 1);
        $entry = $json[$rnd_index];

        $url = $entry['image'];
        $image_part = substr($url, strrpos($url, '/') + 1);
        $better_url = str_replace($image_part, urlencode($image_part), $url);

        return [
            'title'  => $entry['title'],
            'year'   => $entry['yearAsString'],
            'author' => $entry['artistName'],
            'image'  => $better_url
        ];
    }

    public function randomSourceWikiArt()
    {
        $curl = curl_init();

        $seed = mt_rand(1, 599);
        $url = 'https://www.wikiart.org/en/App/Painting/MostViewedPaintings?randomSeed='.$seed.'&amp;json=2&amp;inPublicDomain=true';

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => ""
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  url: '.$url.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  url: '.$url.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            } else {
                $json = json_decode($response, true);
                return $json;
            }
        }
    }

    public function randomSourceInspiroBot()
    {
        $curl = curl_init();

        $url = 'https://inspirobot.me/api?generate=true';

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => ""
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  url: '.$url.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  url: '.$url.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            return $response;
        }
    }

    public function randomSourceQuote()
    {
        $curl = curl_init();

        $url = 'http://quotesondesign.com/wp-json/posts';

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_POSTFIELDS => ""
        ));

        $response = curl_exec($curl);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            $message = ' cURL Error #:' . $err.'  url: '.$url.' response: '.$response;
            $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
        } else {
            if ($httpcode != '200') {
                $message =  ' Http code error #:' . $httpcode.'  url: '.$url.' response: '.$response;
                $this->logdata('['.__METHOD__.' ERROR] '.__FILE__.':'.__LINE__.' '.$message, 1);
            }
            return $response;
        }
    }
}
