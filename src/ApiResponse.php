<?php
namespace KrozamDev\LaravelApiResponse;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class ApiResponse {
    protected $debug = false;
    protected $data = null;
    protected $done = true;
    protected $message;
    protected $code = 200;
    protected $isUpdate;
    protected $isDelete;
    protected $dataFinal = [];
    protected $time;
    protected $customArray = [];

    public function debug(bool $debug=false) : ApiResponse
    {
        $this->debug = $debug;
        return $this;
    }

    public function time() : ApiResponse
    {
        $this->time = Carbon::now();
        return $this;
    }

    public function failed() : ApiResponse
    {
        $this->done = false;
        return $this;
    }

    public function CustomKey(array $array) : ApiResponse
    {
        $this->customArray = $array;
        return $this;
    }

    public function data($data) : ApiResponse
    {
        $this->data = $data;
        return $this;
    }

    public function generate() : JsonResponse
    {
        $this->setStatusCode();
        $this->dataFinal["status"] = $this->done;
        $this->dataFinal["message"] = $this->message;
        if (count($this->customArray)>0) {
            $this->dataFinal = array_merge($this->dataFinal,$this->customArray);
        }
        $setTime = true;
        if (!$this->time) {
            $setTime = false;
            $this->time = Carbon::now();
        }
        $this->dataFinal["time"] = [
            "value"=> $this->time->diffInMilliseconds(Carbon::now())." ms",
            "setTimeBeforeActions"=> $setTime
        ];
        return response()->json($this->dataFinal,$this->code);
    }

    public function isUpdate() : ApiResponse
    {
        $this->isUpdate = true;
        return $this;
    }
    
    public function isDelete() : ApiResponse
    {
        $this->isDelete = true;
        return $this;
    }

    private function setStatusCode() : ApiResponse
    {
        $result = $this->code;
        if (!$this->done){
            if ($this->code == 200) {
                $this->dataFinal["data"] = null;
                if (isset($this->data->status)) {
                    $result = $this->data->status;
                }else{
                    if (method_exists($this->data,'getCode')) {
                        $code = $this->data->getCode();
                        $code = intval($code);
                        $result = $code;
                        if (strlen($code) !== 3) {
                            $result = $code;
                            $result = 500;
                        }
                        if ($code < 100 || $code > 599) {
                            $result = 500;
                        }
                    }else{
                        $result = 500;
                    }
                    if (method_exists($this->data,'getMessage')) {
                        if (preg_match('/already exists|Conflict|Duplicate entry/i',$this->data->getMessage())) {
                            $result = 409;
                        }
                    }
                }
            }
        }else{
            $this->dataFinal["data"] = $this->data;
        }
        $this->dataFinal["status_code"] = $result;
        $this->code = $result;
        $this->setMessage();
        return $this;
    }

    public function code(int $code) : ApiResponse
    {
        $this->code = $code;
        return $this;
    }

    public function message(string $message) : ApiResponse
    {
        $this->message = $message;
        return $this;
    }

    private function setMessage() : void
    {
        if (!$this->message) {
            if (!$this->done) {
                if ($this->debug) {
                    $this->message = $this->data->getMessage();
                }else{
                    switch ($this->code) {
                        case 422:
                            $msg = "Validation error occurred. Please complete the required parameters.";
                            break;

                        case 409:
                            $msg = "Data already exists.";
                            break;
                            
                        case 401:
                        case 404:
                        case 403:
                            $msg = $this->data->getMessage();
                            break;
                        
                        default:
                            $msg = "Internal Server Error.";
                            break;
                    }
                    $this->message = $msg;
                }
            }else{
                $msgRecord = "record successfully";
                if ($this->code == 201) {
                    $this->message = "Created $msgRecord";
                }else{
                    if ($this->code == 200) {
                        $this->message = "get $msgRecord";
                        if ($this->isUpdate) {
                            $this->message = "Updated $msgRecord";
                        }
                        if ($this->isDelete) {
                            $this->message = "Delete $msgRecord";
                        }
                    }else{
                        $this->message = "Unknown Message";
                    }
                }
            }
        }
    }
    
    public function paginationKey($data) : ApiResponse
    {
        if (empty($data['pagination'])) {
            $paginate = $data;
        }else{
            $paginate = $data['pagination'];
        }
        $this->CustomKey(['pagination'=>$paginate]);
        return $this;
    }
}