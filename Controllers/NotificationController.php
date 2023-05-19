<?php

namespace Controllers {

    use Models\FileStoreModel;

    class NotificationController
    {
        public function __construct()
        {
        }

        /**
         * Read file 'notification'
         * unserialize content to FileStoreModel
         * and reverse order to display last notification first
         */
        public function get()
        {
            if (!isset($_COOKIE['token']) || $_COOKIE['token'] != $_ENV['TOKEN']) {
                header('Location: /login');
                return [null, null];
            } else {
                $data = array_reverse(array_map('unserialize', file('notification')));

                return ["list", $data];
            }
        }

        /**
         * Handle post call, parse body to json to get event type
         * Store current date type and raw body in FileStoreModel
         * and write to 'notification' file
         */
        public function post()
        {
            $body = json_decode(file_get_contents('php://input'));
            $webhookurl = $_ENV['DISCORD_WEBHOOK_URL'];
            $webhookurlbal = $_ENV['DISCORD_WEBHOOK_URL_BAL'];
            $urls = [$webhookurl];
            $messages = [];
            $fun = [
                "Hip hip hip, hourra !",
                "Je pète ma bière, ma lubulule",
                "Mais c'est tout simplement une hallucination collective",
                "Parce que c'est notre projet",
                "Mais voilà mais c'était sûr en fait",
                "Véronique fait un infarctus",
                "Mais oui c'est clair",
                "Encore, ça fait beaucoup là non ? (non)",
                "Je possède des thunes (ouais), je suis à l'aise financièrement",
                "Bien évidemment, bien évidemment, bien évidemmeeeeeeent",
                "Woaw qu'est-ce que c'est que ce truc là ?",
                "Ohhhh, j'ai le droit de vivre un peu ?",
                "Par la poudre de perlimpinpin",
                "T'es qui toi ? Beh je suis Pamela",
                "Ouh lala c'est la décadenceinnn"
            ];

            if ($body->eventType === "Order") {

                if ($body->data->formSlug === "bal-de-promo-2023") {
                    array_push($urls, $webhookurl);
                    $timestamp = date("c", strtotime("now"));

                    $disc = [
                        "username" => "MDL - Bal de Promo",
                        "avatar_url" =>
                        "https://cdn.helloasso.com/img/logos/croppedimage-234b7b32a4ab4e269abee0c035e3f36c.png?resize=fill:140:140",
                        "tts" => false,

                        // File upload
                        // "file" => "",

                        // Embeds Array
                        "embeds" => [
                            [
                                // Embed Title
                                "title" => "Nouvelle vente pour le bal de promo",

                                // Embed Type
                                "type" => "rich",

                                // Embed Description
                                "description" => "**" . $body->data->payer->firstName . " "
                                . $body->data->payer->lastName 
                                . "** vient de passer une commande pour le bal de promo d'un total de **"
                                . strval($body->data->amount->total / 100) . "€** !\n\n**Détail de la commande :**",

                                "fields" => [],

                                // Timestamp of embed must be formatted as ISO8601
                                "timestamp" => $timestamp,

                                // Embed left border color in HEX
                                "color" => hexdec("ffffff"),

                                // Author
                                "author" => [
                                    "name" => $fun[array_rand($fun)]
                                ]
                            ]

                        ]

                    ];

                    foreach ($body->data->items as $item) {
                        if ($item->type === "Donation") {
                            array_push($disc["embeds"][0]["fields"], [
                                "name" => "Don",
                                "value" => "Don pour la MDL de **" . strval($item->amount / 100) . "€**"
                            ]);
                        } else {
                            array_push($disc["embeds"][0]["fields"], [
                                "name" => $item->name,
                                "value" => "Pour **" . $item->user->firstName . " " . $item->user->lastName . "** en **" . $item->customFields[2]->answer . "**\n" 
                                . "Participation à l'élection : **" . $item->customFields[0]->answer . "**"
                            ]);
                        };
                    }

                    array_push($messages, json_encode($disc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                    array_push($messages, json_encode($disc, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
                }
            }

            foreach ($urls as $key => $value) {
                $ch = curl_init($value);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $messages[$key]);
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                $response = curl_exec($ch);
                curl_close($ch);
            }

            date_default_timezone_set('	Europe/Paris');

            $model = new FileStoreModel();
            $model->date = date("d/m/Y H:i:s");
            $model->type = $body->eventType;
            $model->data = json_encode($body);

            file_put_contents('notification', serialize($model) . PHP_EOL, FILE_APPEND);

            return [null, null];
        }

        public function getAuth()
        {

            if (isset($_COOKIE['token']) && $_COOKIE['token'] == $_ENV['TOKEN']) {
                header('Location: /');
                return [null, null];
            } else {
                return ["login", null];
            }

        }


        public function postAuth()
        {

            if (isset($_COOKIE['token']) && $_COOKIE['token'] == $_ENV['TOKEN']) {
                header('Location: /');
                return [null, null];
            } else if (isset($_POST['username']) && isset($_POST['password'])) {
                if ($_POST['username'] == $_ENV['USERNAME'] && $_POST['password'] == $_ENV['PASSWORD']) {
                    setcookie('token', $_ENV['TOKEN'], time() + 3600, '/');
                    header('Location: /');
                    return [null, null];
                } else {
                    return ["login", null];
                }
            } else {
                return ["login", null];
            }

        }
    }
}

?>