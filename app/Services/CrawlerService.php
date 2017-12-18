<?php
namespace App\Services;

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    /** @var Client  */
    private $client;

    public function __construct()
    {
        $this->client = app(Client::class);
    }

    /**
     * @param string $path
     * @return Crawler
     */
    public function getOriginalData(string $path): Crawler
    {
        $content = $this->client->get($path)->getBody()->getContents();
        $crawler = new Crawler();

        $crawler->addHtmlContent($content);

        return $crawler;
    }

    /**
     * @copyright http://www.jcodecraeer.com/a/phpjiaocheng/2015/1006/3545.html
     * @param string $target
     * @return string
     */
    public function getFilterString(string $target): string
    {
        $target = str_replace("\r", "", $target);
        $target = str_replace("\n", "", $target);
        $target = str_replace("\t", "", $target);
        $target = str_replace("\r\n", "", $target);
        $target = preg_replace("/\s+/", " ", $target);
        $target = preg_replace("/<[ ]+/si", "<", $target);
        $target = preg_replace("/<\!--.*?-->/si", "", $target);
        $target = preg_replace("/<(\!.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?html.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?head.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?meta.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?body.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?link.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?form.*?)>/si", "", $target);
        $target = preg_replace("/cookie/si", "COOKIE", $target);
        $target = preg_replace("/<(applet.*?)>(.*?)<(\/applet.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?applet.*?)>/si", "", $target);
        $target = preg_replace("/<(style.*?)>(.*?)<(\/style.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?style.*?)>/si", "", $target);
        $target = preg_replace("/<(title.*?)>(.*?)<(\/title.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?title.*?)>/si", "", $target);
        $target = preg_replace("/<(object.*?)>(.*?)<(\/object.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?objec.*?)>/si", "", $target);
        $target = preg_replace("/<(noframes.*?)>(.*?)<(\/noframes.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?noframes.*?)>/si", "", $target);
        $target = preg_replace("/<(i?frame.*?)>(.*?)<(\/i?frame.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?i?frame.*?)>/si", "", $target);
        $target = preg_replace("/<(script.*?)>(.*?)<(\/script.*?)>/si", "", $target);
        $target = preg_replace("/<(\/?script.*?)>/si", "", $target);
        $target = preg_replace("/javascript/si", "Javascript", $target);
        $target = preg_replace("/vbscript/si", "Vbscript", $target);
        $target = preg_replace("/on([a-z]+)\s*=/si", "On\\1=", $target);
        $target = preg_replace("/&#/si", "&＃", $target);

        $pat = "/<(\/?)(script|i?frame|style|html|body|li|i|map|title|img|".
            "link|span|u|font|table|tr|b|marquee|td|strong|div|a|meta|\?|\%)([^>]*?)>/isU";
        $target = preg_replace($pat, "", $target);

        return trim($target);
    }

    /**
     * @param Crawler $crawler
     * @return array
     */
    public function getNewEpisodeFromComic99770(Crawler $crawler): array
    {
        $imagePath = $crawler->filterXPath('//div[@class="cDefaultImg"]/img')->attr('src');
        $directUri = $crawler->filterXPath('//table[@class="cInfoTxt"]/tr[5]/td[2]/a')->attr('href');
        $label = $crawler->filterXPath('//table[@class="cInfoTxt"]/tr[5]/td[2]/a')->text();
        $date = $crawler->evaluate('substring-after(//table[@class="cInfoTxt"]/tr[5]/td[2], ":")');

        return [
            'date' => array_first($date),
            'directUri' => $directUri,
            'imagePath' => $imagePath,
            'label' => $this->getFilterString($label),
        ];
    }


    /**
     * @param Crawler $crawler
     * @return array
     */
    public function getNewAnimationFromBaHa(Crawler $crawler): array
    {
        $target = $crawler->filterXPath('//div[contains(@class, "newanime")]')
            ->each(function (Crawler $node) {
                $date = $this->getDateForNewAnimationFromBaHa($node);
                $link = $this->getLinkForNewAnimationFromBaHa($node);
                $image = $this->getImageForNewAnimationFromBaHa($node);

                $response = [
                    'date' => array_first($date),
                    'directUri' => array_first($link),
                    'imagePath' => array_first($image),
                ];

                if (null !== $response['directUri']) {
                    $crawler = $this->getOriginalData($response['directUri']);
                    $response = array_merge($response, $this->getAuthorInformationFromBaHa($crawler));
                    $response['label'] = $this->getTitleForNewAnimationFromBaHa($crawler);
                    $response['text'] = $this->getTextForNewAnimationFromBaHa($crawler);
                }

                return in_array(null, array_values($response), true) ? null : $response;
            });
        $target = array_filter($target, function ($d) {
            return null !== $d;
        });
        return $target;
    }

    private function getTextForNewAnimationFromBaHa(Crawler $node)
    {
        return $node->filterXPath('//div[@class="data_intro"]/p')->text();
    }

    private function getAuthorInformationFromBaHa(Crawler $node)
    {
        $authorLink = $node->filterXPath('//div[@class="logo"]/a')->attr('href');
        $authorIcon = $node->filterXPath('//div[@class="logo"]/a/img')->attr('src');

        return [
            'authorName' => str_replace('//', '', $authorLink),
            'authorLink' => str_replace('//', 'https://', $authorLink),
            'authorIcon' => $authorIcon,
        ];
    }

    private function getTitleForNewAnimationFromBaHa(Crawler $node)
    {
        return $node->filterXPath('//div[@class="anime_name"]/h1')->text();
    }

    private function getDateForNewAnimationFromBaHa(Crawler $node)
    {
        return $node->filterXPath('//span[contains(@class, "newanime-date")]')
            ->each(function (Crawler $node) {
                return $node->text();
            });
    }

    private function getLinkForNewAnimationFromBaHa(Crawler $node)
    {
        return $node->filterXPath('//a[contains(@class, "newanime__content")]')
            ->evaluate('string(@href)');
    }

    private function getImageForNewAnimationFromBaHa(Crawler $node)
    {
        return $node->filterXPath('//img[contains(@class, "lazyload")]')
            ->evaluate('string(@data-src)');
    }
}
