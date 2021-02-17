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

            // This is here where you can add your own logic
            // You can get all data from $body->data->...

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