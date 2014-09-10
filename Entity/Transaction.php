<?php

namespace Caxy\Bundle\RecurlyBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transaction
 *
 * @ORM\Table("recurly_transaction")
 * @ORM\Entity
 */
class Transaction
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="guid")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var boolean
     *
     * @ORM\Column(name="semaphore", type="boolean")
     */
    private $semaphore;

    public function __construct($id, \DateTime $date, $semaphore = true)
    {
        $this->id = $id;
        $this->date = $date;
        $this->semaphore = $semaphore;
    }

    /**
     * Get uuid
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set semaphore
     *
     * @param boolean $semaphore
     *
     * @return Transaction
     */
    public function setSemaphore($semaphore)
    {
        $this->semaphore = $semaphore;

        return $this;
    }

    /**
     * Get locked
     *
     * @return boolean
     */
    public function getSemaphore()
    {
        return $this->semaphore;
    }
}
