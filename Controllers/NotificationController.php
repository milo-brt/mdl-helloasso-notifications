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
            $webhookurl = getenv('DISCORD_WEBHOOK_URL');

            // This is here where you can add your own logic
            // You can get all data from $body->data->...

            if ($body->eventType === "Payment") {
                $timestamp = date("c", strtotime("now"));

                $json_data = json_encode([
                    "content" => "Un nouveau paiement à été effectué sur la boutique en ligne !",
                    
                    // Username
                    "username" => "MDL - Boutique",

                    // Avatar URL.
                    // Uncoment to replace image set in webhook
                    "avatar_url" => "https://cdn.helloasso.com/img/logos/croppedimage-234b7b32a4ab4e269abee0c035e3f36c.png?resize=fill:140:140",

                    // Text-to-speech
                    "tts" => false,

                    // File upload
                    // "file" => "",

                    // Embeds Array
                    "embeds" => [
                        [
                            // Embed Title
                            "title" => "PHP - Send message to Discord (embeds) via Webhook",

                            // Embed Type
                            "type" => "rich",

                            // Embed Description
                            "description" => "Description will be here, someday, you can mention users here also by calling userID <@12341234123412341>",

                            // URL of title link
                            "url" => "https://gist.github.com/Mo45/cb0813cb8a6ebcd6524f6a36d4f8862c",

                            // Timestamp of embed must be formatted as ISO8601
                            "timestamp" => $timestamp,

                            // Embed left border color in HEX
                            "color" => hexdec( "3366ff" ),

                            // Footer
                            "footer" => [
                                "text" => "GitHub.com/Mo45",
                                "icon_url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=375"
                            ],

                            // Image to send
                            "image" => [
                                "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=600"
                            ],

                            // Thumbnail
                            //"thumbnail" => [
                            //    "url" => "https://ru.gravatar.com/userimage/28503754/1168e2bddca84fec2a63addb348c571d.jpg?size=400"
                            //],

                            // Author
                            "author" => [
                                "name" => "krasin.space",
                                "url" => "https://krasin.space/"
                            ],

                            // Additional Fields array
                            "fields" => [
                                // Field 1
                                [
                                    "name" => "Field #1 Name",
                                    "value" => "Field #1 Value",
                                    "inline" => false
                                ],
                                // Field 2
                                [
                                    "name" => "Field #2 Name",
                                    "value" => "Field #2 Value",
                                    "inline" => true
                                ]
                                // Etc..
                            ]
                        ]
                    ]

                ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );


                $ch = curl_init( $webhookurl );
                curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
                curl_setopt( $ch, CURLOPT_POST, 1);
                curl_setopt( $ch, CURLOPT_POSTFIELDS, $json_data);
                curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
                curl_setopt( $ch, CURLOPT_HEADER, 0);
                curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

                $response = curl_exec( $ch );
                // If you need to debug, or find out why you can't send message uncomment line below, and execute script.
                // echo $response;
                curl_close( $ch );
            }

            $model = new FileStoreModel();
            $model->date = date("d/m/Y H:i:s");
            $model->type = $webhookurl;
            $model->data = json_encode($body);

            file_put_contents('notification',  serialize($model) . PHP_EOL, FILE_APPEND);
            
            return [null, null];
        }
    }
}

?>