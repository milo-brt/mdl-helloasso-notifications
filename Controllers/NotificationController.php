<?php

namespace Controllers
{

    use Models\FileStoreModel;

    class NotificationController
    {
        public function __construct()
        { }

        /**
         * Read file 'notification'
         * unserialize content to FileStoreModel
         * and reverse order to display last notification first
         */
        public function get()
        {
            $data = array_reverse(array_map('unserialize', file('notification')));

            return ["list", $data];
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
            $ch = curl_init( $webhookurl );

            $json_data = "";

            if ($body->eventType === "Order") {

                if ($body->data->order->formSlug === "bal-de-promo-2023") {
                    $ch = curl_init($webhookurlbal);
                
                    $timestamp = date("c", strtotime("now"));

                    $json_data = json_encode([
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
                                "description" => $body->data->payer->firstName . " " . $body->data->payer->lastName . " vient de passer une commande pour le bal de promo 2023 !",

                                // URL of title link
                                "url" => $body->data->payments[0]->paymentReceiptUrl,

                                // Timestamp of embed must be formatted as ISO8601
                                "timestamp" => $timestamp,

                                // Embed left border color in HEX
                                "color" => hexdec( "ffffff" ),

                                // Image to send
                                "image" => [
                                    "url" => "https://media.giphy.com/media/U4DswrBiaz0p67ZweH/giphy.gif"
                                ],

                                // Author
                                "author" => [
                                    "name" => "Hip hip hip, hourra !"
                                ]
                            ]
                            
                        ]

                    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
                }
            }

            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
            curl_setopt( $ch, CURLOPT_POST, 1);
            curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
            curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt( $ch, CURLOPT_HEADER, 0);
            curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec( $ch );
            curl_close( $ch );

            $model = new FileStoreModel();
            $model->date = date("d/m/Y H:i:s");
            $model->type = $body->eventType;
            $model->data = json_encode($body);

            file_put_contents('notification',  serialize($model) . PHP_EOL, FILE_APPEND);
            
            return [null, null];
        }
    }
}

?>