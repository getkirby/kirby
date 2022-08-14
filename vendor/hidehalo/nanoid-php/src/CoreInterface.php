<?php
namespace Hidehalo\Nanoid;

interface CoreInterface
{
    const SAFE_SYMBOLS = '_-0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    const MASKS = [15, 31, 63, 127, 255];

    /**
     * Secure random string generator with custom alphabet.
     * Alphabet must contain 256 symbols or less. Otherwise, the generator
     * will not be secure.
     *
     * @param GeneratorInterface $generator
     * @param string $alphabet
     * @param integer $size
     */
    public function random(GeneratorInterface $generator, $size, $alphabet = CoreInterface::SAFE_SYMBOLS);
}
