<?php
declare(strict_types = 1);

namespace Innmind\Signals;

final class Info
{
    private ?Signal\Code $code;
    private ?Signal\ErrorNumber $errorNumber;
    private ?Signal\SendingProcessId $sendingProcessId;
    private ?Signal\SendingProcessUserId $sendingProcessUserId;
    private ?Signal\Status $status;

    public function __construct(
        Signal\Code $code = null,
        Signal\ErrorNumber $errorNumber = null,
        Signal\SendingProcessId $sendingProcessId = null,
        Signal\SendingProcessUserId $sendingProcessUserId = null,
        Signal\Status $status = null
    ) {
        $this->code = $code;
        $this->errorNumber = $errorNumber;
        $this->sendingProcessId = $sendingProcessId;
        $this->sendingProcessUserId = $sendingProcessUserId;
        $this->status = $status;
    }

    public function code(): ?Signal\Code
    {
        return $this->code;
    }

    public function errorNumber(): ?Signal\ErrorNumber
    {
        return $this->errorNumber;
    }

    public function sendingProcessId(): ?Signal\SendingProcessId
    {
        return $this->sendingProcessId;
    }

    public function sendingProcessUserId(): ?Signal\SendingProcessUserId
    {
        return $this->sendingProcessUserId;
    }

    public function status(): ?Signal\Status
    {
        return $this->status;
    }
}
