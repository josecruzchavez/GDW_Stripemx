<?php
namespace GDW\Stripemx\Model;

use GDW\Stripemx\Helper\Data;
use Magento\Framework\Encryption\EncryptorInterface;

class StripemxCard
{
    protected EncryptorInterface $enc;
    protected Data $help;

    private function getStringConfig(string $field): string
    {
        $value = $this->help->getDirectVal($field);
        if (is_scalar($value) || (is_object($value) && method_exists($value, '__toString'))) {
            return (string) $value;
        }

        return '';
    }

    private function getBoolConfig(string $field): bool
    {
        $value = $this->help->getDirectVal($field);
        if (is_bool($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return $value != 0;
        }
        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            if ($normalized === '1' || $normalized === 'true' || $normalized === 'yes' || $normalized === 'on') {
                return true;
            }
            if ($normalized === '0' || $normalized === 'false' || $normalized === 'no' || $normalized === 'off' || $normalized === '') {
                return false;
            }
        }

        return false;
    }

    public function __construct(
        Data $help, 
        EncryptorInterface $enc
    )
    {
        $this->enc = $enc;
        $this->help = $help;
    }

    public function enable(): bool
    {
        return $this->getBoolConfig('active');
    }

    public function isDebug(): bool
    {
        return $this->getBoolConfig('debug');
    }


    public function note(): string
    {
        return $this->getStringConfig('note');
    }

    public function notemsi(): string
    {
        return $this->getStringConfig('note_msi');
    }

    public function globalerrorshow(): string
    {
        return $this->getStringConfig('global_error_show');
    }

    public function sandbox(): bool
    {
        return $this->getBoolConfig('sandbox_mode');
    }

    public function keyPublic(): string
    {
        return $this->sandbox() ? $this->getStringConfig('key_public_sandbox') : $this->getStringConfig('key_public_live');
    }

    public function keySecret(): string
    {
        $getToken = $this->sandbox() ? $this->getStringConfig('key_secret_sandbox') : $this->getStringConfig('key_secret_live');
        return $this->enc->decrypt($getToken);
    }

    public function getCoutas(): string
    {
        return $this->getStringConfig('payment_limit');
    }

    public function getStripeScript(): ?string
    {
        $script = $this->help->getDirectVal('stripe_script');
        if ($script === null) {
            return null;
        }

        if (is_scalar($script) || (is_object($script) && method_exists($script, '__toString'))) {
            return (string) $script;
        }

        return null;
    }

    public function setLogs(string $desc, mixed $data): void
    {
        if($this->isDebug()){
            try {
                $this->help->log($desc);
                $this->help->log($data);
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

}
