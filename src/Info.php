<?php
declare(strict_types = 1);

namespace Innmind\Signals;

use Innmind\Immutable\Maybe;

final class Info
{
    /** @var Maybe<Signal\Code> */
    private Maybe $code;
    /** @var Maybe<Signal\ErrorNumber> */
    private Maybe $errorNumber;
    /** @var Maybe<Signal\SendingProcessId> */
    private Maybe $sendingProcessId;
    /** @var Maybe<Signal\SendingProcessUserId> */
    private Maybe $sendingProcessUserId;
    /** @var Maybe<Signal\Status> */
    private Maybe $status;

    /**
     * @param Maybe<Signal\Code> $code
     * @param Maybe<Signal\ErrorNumber> $errorNumber
     * @param Maybe<Signal\SendingProcessId> $sendingProcessId
     * @param Maybe<Signal\SendingProcessUserId> $sendingProcessUserId
     * @param Maybe<Signal\Status> $status
     */
    public function __construct(
        Maybe $code,
        Maybe $errorNumber,
        Maybe $sendingProcessId,
        Maybe $sendingProcessUserId,
        Maybe $status,
    ) {
        $this->code = $code;
        $this->errorNumber = $errorNumber;
        $this->sendingProcessId = $sendingProcessId;
        $this->sendingProcessUserId = $sendingProcessUserId;
        $this->status = $status;
    }

    /**
     * @return Maybe<Signal\Code>
     */
    public function code(): Maybe
    {
        return $this->code;
    }

    /**
     * @return Maybe<Signal\ErrorNumber>
     */
    public function errorNumber(): Maybe
    {
        return $this->errorNumber;
    }

    /**
     * @return Maybe<Signal\SendingProcessId>
     */
    public function sendingProcessId(): Maybe
    {
        return $this->sendingProcessId;
    }

    /**
     * @return Maybe<Signal\SendingProcessUserId>
     */
    public function sendingProcessUserId(): Maybe
    {
        return $this->sendingProcessUserId;
    }

    /**
     * @return Maybe<Signal\Status>
     */
    public function status(): Maybe
    {
        return $this->status;
    }
}
