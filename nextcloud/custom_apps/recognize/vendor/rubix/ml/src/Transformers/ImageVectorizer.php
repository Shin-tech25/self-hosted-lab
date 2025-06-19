<?php

namespace OCA\Recognize\Vendor\Rubix\ML\Transformers;

use OCA\Recognize\Vendor\Rubix\ML\DataType;
use OCA\Recognize\Vendor\Rubix\ML\Helpers\Params;
use OCA\Recognize\Vendor\Rubix\ML\Datasets\Dataset;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\ExtensionIsLoaded;
use OCA\Recognize\Vendor\Rubix\ML\Specifications\SamplesAreCompatibleWithTransformer;
use OCA\Recognize\Vendor\Rubix\ML\Exceptions\RuntimeException;
use function is_null;
/**
 * Image Vectorizer
 *
 * Image Vectorizer takes images of the same size and converts them into flat feature vectors
 * of raw color channel intensities. Intensities range from 0 to 255 and can either be read
 * from 1 channel (grayscale) or 3 channels (RGB color) per pixel.
 *
 * > **Note**: The GD extension is required to use this transformer.
 *
 * @category    Machine Learning
 * @package     Rubix/ML
 * @author      Andrew DalPino
 * @internal
 */
class ImageVectorizer implements Transformer, Stateful
{
    /**
     * Encode the images as grayscale?
     *
     * @var bool
     */
    protected bool $grayscale;
    /**
     * The fixed width and height of the images for each image feature column.
     *
     * @var array<int[]>|null
     */
    protected ?array $sizes = null;
    /**
     * @param bool $grayscale
     */
    public function __construct(bool $grayscale = \false)
    {
        ExtensionIsLoaded::with('gd')->check();
        $this->grayscale = $grayscale;
    }
    /**
     * Return the data types that this transformer is compatible with.
     *
     * @internal
     *
     * @return list<\OCA\Recognize\Vendor\Rubix\ML\DataType>
     */
    public function compatibility() : array
    {
        return DataType::all();
    }
    /**
     * Is the transformer fitted?
     *
     * @return bool
     */
    public function fitted() : bool
    {
        return isset($this->sizes);
    }
    /**
     * Fit the transformer to a dataset.
     *
     * @param Dataset $dataset
     * @throws \OCA\Recognize\Vendor\Rubix\ML\Exceptions\InvalidArgumentException
     */
    public function fit(Dataset $dataset) : void
    {
        SamplesAreCompatibleWithTransformer::with($dataset, $this)->check();
        $sample = $dataset->sample(0);
        $this->sizes = [];
        foreach ($dataset->featureTypes() as $column => $type) {
            if ($type->isImage()) {
                $value = $sample[$column];
                $this->sizes[$column] = [\imagesx($value), \imagesy($value)];
            }
        }
    }
    /**
     * Transform the dataset in place.
     *
     * @param list<list<mixed>> $samples
     * @throws RuntimeException
     */
    public function transform(array &$samples) : void
    {
        if (is_null($this->sizes)) {
            throw new RuntimeException('Transformer has not been fitted.');
        }
        foreach ($samples as &$sample) {
            $vectors = [];
            foreach ($this->sizes as $column => [$width, $height]) {
                $value = $sample[$column];
                $vector = [];
                for ($x = 0; $x < $width; ++$x) {
                    for ($y = 0; $y < $height; ++$y) {
                        $pixel = \imagecolorat($value, $x, $y);
                        $vector[] = $pixel & 0xff;
                        if (!$this->grayscale) {
                            $vector[] = $pixel >> 8 & 0xff;
                            $vector[] = $pixel >> 16 & 0xff;
                        }
                    }
                }
                unset($sample[$column]);
                \imagedestroy($value);
                $vectors[] = $vector;
            }
            $sample = \array_merge($sample, ...$vectors);
        }
    }
    /**
     * Return the string representation of the object.
     *
     * @internal
     *
     * @return string
     */
    public function __toString() : string
    {
        return 'Image Vectorizer (grayscale: ' . Params::toString($this->grayscale) . ')';
    }
}
