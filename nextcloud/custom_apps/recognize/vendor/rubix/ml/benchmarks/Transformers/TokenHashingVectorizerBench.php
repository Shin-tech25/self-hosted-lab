<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Benchmarks\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\Datasets\Unlabeled;
use OCA\Recognize\Vendor\Rubix\ML\Transformers\TokenHashingVectorizer;
/**
 * @Groups({"Transformers"})
 * @BeforeMethods({"setUp"})
 * @internal
 */
class TokenHashingVectorizerBench
{
    protected const DATASET_SIZE = 2500;
    protected const SAMPLE_TEXT = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec at nisl posuere, luctus sapien vel, maximus ex. Curabitur tincidunt, libero at commodo tempor, magna neque malesuada diam, vel blandit metus velit quis magna. Vestibulum auctor libero quam, eu ullamcorper nulla dapibus a. Mauris id ultricies sapien. Integer consequat mi eget vehicula vulputate. Mauris cursus nisi non semper dictum. Quisque luctus ex in tortor laoreet tincidunt. Vestibulum imperdiet purus sit amet sapien dignissim elementum. Mauris tincidunt eget ex eu laoreet. Etiam efficitur quam at purus sagittis hendrerit. Mauris tempus, sem in pulvinar imperdiet, lectus ipsum molestie ante, id semper nunc est sit amet sem. Nulla at justo eleifend, gravida neque eu, consequat arcu. Vivamus bibendum eleifend metus, id elementum orci aliquet ac. Praesent pellentesque nisi vitae tincidunt eleifend. Pellentesque quis ex et lorem laoreet hendrerit ut ac lorem. Aliquam non sagittis est.';
    /**
     * @var \OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset
     */
    protected $dataset;
    /**
     * @var TokenHashingVectorizer
     */
    protected $transformer;
    /**
     * @var list<list<string>>
     */
    protected $aSamples;
    /**
     * @var list<list<string>>
     */
    protected $bSamples;
    public function setUp() : void
    {
        $samples = [];
        for ($i = 0; $i < self::DATASET_SIZE; ++$i) {
            $text = self::SAMPLE_TEXT;
            $samples[] = [\str_shuffle($text)];
        }
        $this->dataset = Unlabeled::quick($samples);
        $this->transformer = new TokenHashingVectorizer(1000, null, TokenHashingVectorizer::CRC32);
    }
    /**
     * @Subject
     * @Iterations(5)
     * @OutputTimeUnit("milliseconds", precision=3)
     */
    public function apply() : void
    {
        $this->dataset->apply($this->transformer);
    }
}
