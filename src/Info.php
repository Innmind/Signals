<?php
declare(strict_types = 1);

namespace Innmind\Signals;

use Innmind\Immutable\Maybe;

/**
 * @psalm-immutable
 */
final class Info
{
    /**
     * @param Maybe<Signal\Code> $code
     * @param Maybe<Signal\ErrorNumber> $errorNumber
     * @param Maybe<Signal\SendingProcessId> $sendingProcessId
     * @param Maybe<Signal\SendingProcessUserId> $sendingProcessUserId
     * @param Maybe<Signal\Status> $status
     */
    private function __construct(
        private Maybe $code,
        private Maybe $errorNumber,
        private Maybe $sendingProcessId,
        private Maybe $sendingProcessUserId,
        private Maybe $status,
    ) {
    }

    /**
     * @internal
     *
     * @param Maybe<Signal\Code> $code
     * @param Maybe<Signal\ErrorNumber> $errorNumber
     * @param Maybe<Signal\SendingProcessId> $sendingProcessId
     * @param Maybe<Signal\SendingProcessUserId> $sendingProcessUserId
     * @param Maybe<Signal\Status> $status
     */
    public static function of(
        Maybe $code,
        Maybe $errorNumber,
        Maybe $sendingProcessId,
        Maybe $sendingProcessUserId,
        Maybe $status,
    ): self {
        return new self(
            $code,
            $errorNumber,
            $sendingProcessId,
            $sendingProcessUserId,
            $status,
        );
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
