<?php
/**
 * Utility trait
 *
 * @category UtilityTrait
 * @author   Codal <developer@codal.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://local.legionmedia.com/
 */

namespace App\Traits;

use App\Library\CustomORM\AppCollection;
use Mail;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Utility trait
 *
 * @category UtilityTrait
 * @author   Codal <developer@codal.com>
 * @license  http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @link     http://local.legionmedia.com/
 */
trait UtilityTrait
{
    /**
     * Method get value by given key from array.
     *
     * @param string     $key     key name
     * @param array      $arr     array
     * @param mixed|null $default default flag
     *
     * @return mixed
     */
    public function arrayGet(string $key, array $arr, $default = null)
    {
        if (is_array($arr) && array_key_exists($key, $arr) && !empty($arr[$key])) {
            return $arr[$key];
        }
        return $default;
    }

    /**
     * This will send mail
     *
     * @param string $toEmail  ToEmail
     * @param string $mailFrom MailFrom
     * @param string $mailName MailName
     * @param string $body     Body
     * @param string $subject  Subject
     *
     * @return void
     */
    public function sendMail(
        string $toEmail,
        string $mailFrom,
        string $mailName,
        array $body,
        string $subject,
        string $fileName
    ) {
        try {
            Mail::send(
                $fileName,
                ['body' => $body],
                function ($message) use ($toEmail, $body, $mailFrom, $mailName, $subject) {
                    $message->to($toEmail)->subject($subject);
                    $message->from($mailFrom, $mailName);
                    if ($this->arrayGet('filepath', $body, 0)) {
                        $message->attach(
                            $body['filepath'],
                            array(
                                'as' => $body['file_name'],
                                'mime' => 'csv',
                            )
                        );
                    }
                }
            );
            return ["message" => "Send successfully", "error" => 0];
        } catch (\Swift_TransportException $transportExp) {
            return ["message" => $transportExp->getMessage(), "error" => 1];
        }
    }

    /**
     * This will retrive table name
     *
     * @return string Table Name
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     * Converts an object to an array
     *
     * @param object $d object to be converted
     *
     * @return array Array convertido
     */
    public function objectToArray($d)
    {
        if (is_object($d)) {
            $d = get_object_vars($d);
        }
        return is_array($d) ? array_map(array($this, 'objectToArray'), $d) : $d;
    }

    // /**
    //  * Passing model name for the pagination and sorting.
    //  *
    //  */
    // public function newCollection(array $models = [])
    // {
    //     return new AppCollection($models);
    // }

    /*
     * Generate string message from array
     *
     * @param array $message Message
     *
     * @return string          output
     */
    public function generateMessage(array $message)
    {
        $output = implode(
            ', ',
            array_map(
                function ($v, $k) {
                    return sprintf("%s %s ", $k, $v);
                },
                $message,
                array_keys($message)
            )
        );
        return $output;
    }

    /**
     * This will paginate data for API response
     *
     * @param object $data      model object
     * @param string $sortBy    sortBy key
     * @param string $perpage   perpage records
     * @param string $direction sort direction
     * @param string $page      page
     *
     * @return mixed
     */
    // public function getPaginated(
    //     $data,
    //     string $sortBy = null,
    //     string $perpage = null,
    //     bool $direction = true,
    //     string $page = null,
    //     array $numericSort = [],
    //     bool $checkSort = true
    // ) {
    //     try {
    //         //$numericSort -array containing fields to be handled as numeric sort type
    //         array_push($numericSort, 'id');
    //         $sortType = SORT_NUMERIC;
    //         $sortBy = $sortBy ?? 'id';

    //         if (!empty($sortBy) && !in_array($sortBy, $numericSort)) {
    //             $sortType = SORT_STRING | SORT_FLAG_CASE;
    //         }
    //         $data = ($data instanceof \App\Library\CustomORM\AppCollection) ? $data : $data->get();
    //         $data = $data->take(1000);

    //         $data = $checkSort ? $data->sortBy($sortBy, $sortType, $direction) : $data;
    //         $dataCount = $data->count();

    //         if (empty($perpage)) {
    //             $perpage = $dataCount ? $dataCount : 10;
    //             if (!empty($page)) {
    //                 $perpage = $dataCount ? $dataCount : 10;
    //             }
    //         }
    //         $data = $data->paginate($perpage);
    //         $data->appends(request()->query())->links();

    //         return $data;
    //     } catch (\Exception $exception) {
    //         throw new \Exception($exception->getMessage());
    //     }
    // }

    /**
     * This will return api key
     *
     * @param int $length Length
     *
     * @return string
     */
    public function getApiKey(int $length)
    {
        return
        substr(
            str_shuffle(
                config(
                    'constants.alphaNumericCharsStr'
                )
            ),
            0,
            $length
        );
    }
}
