<?php

namespace OCA\Recognize\Vendor\Tensor\Decompositions;

use OCA\Recognize\Vendor\Tensor\Matrix;
use OCA\Recognize\Vendor\Tensor\Exceptions\NotImplemented;
/**
 * SVD
 *
 * @category    Scientific Computing
 * @package     Rubix/Tensor
 * @author      Andrew DalPino
 * @internal
 */
class SVD
{
    /**
     * The U matrix.
     *
     * @var Matrix
     */
    protected Matrix $u;
    /**
     * The singular values of the matrix A.
     *
     * @var list<float>
     */
    protected array $singularValues;
    /**
     * The V transposed matrix.
     *
     * @var Matrix
     */
    protected Matrix $vT;
    /**
     * Factory method to decompose a matrix.
     *
     * @param Matrix $a
     * @return self
     */
    public static function decompose(Matrix $a) : self
    {
        throw new NotImplemented('SVD is not implemented in Tensor PHP.');
    }
    /**
     * @param Matrix $u
     * @param list<int|float> $singularValues
     * @param Matrix $vT
     */
    public function __construct(Matrix $u, array $singularValues, Matrix $vT)
    {
        $this->u = $u;
        $this->singularValues = $singularValues;
        $this->vT = $vT;
    }
    /**
     * Return the U matrix.
     *
     * @return Matrix
     */
    public function u() : Matrix
    {
        return $this->u;
    }
    /**
     * Return the singular values of matrix A.
     *
     * @return list<float>
     */
    public function singularValues() : array
    {
        return $this->singularValues;
    }
    /**
     * Return the singular value matrix.
     *
     * @return Matrix
     */
    public function s() : Matrix
    {
        return Matrix::diagonal($this->singularValues);
    }
    /**
     * Return the V matrix.
     *
     * @return Matrix
     */
    public function v() : Matrix
    {
        return $this->vT->transpose();
    }
    /**
     * Return the V transposed matrix.
     *
     * @return Matrix
     */
    public function vT() : Matrix
    {
        return $this->vT;
    }
}
