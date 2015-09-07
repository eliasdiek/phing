<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information please see
 * <http://phing.info>.
 */
namespace Phing\Task\System;

use Exception;
use Phing\Exception\BuildException;
use Phing\Task;


/**
 * A wrapper task that lets you run tasks(s) when another set
 * of tasks fails.
 *
 * Inspired by {@link http://ant-contrib.sourceforge.net/tasks/tasks/trycatch.html}
 *
 * @author   Michiel Rook <mrook@php.net>
 * @version  $Id$
 * @package  phing.tasks.system
 */
class TryCatch extends Task
{
    protected $propertyName = "";
    protected $referenceName = '';

    /**
     * @var Sequential
     */
    protected $tryContainer = null;

    /**
     * @var Sequential
     */
    protected $catchContainer = null;

    /**
     * @var Sequential
     */
    protected $finallyContainer = null;

    /**
     * Main method
     *
     * @throws BuildException
     * @return void
     */
    public function main()
    {
        $exc = null;

        if (empty($this->tryContainer)) {
            throw new BuildException('A nested <try> element is required');
        }

        try {
            $this->tryContainer->perform();
        } catch (BuildException $e) {
            if (!empty($this->propertyName)) {
                $this->project->setProperty($this->propertyName, $e->getMessage());
            }

            if (!empty($this->referenceName)) {
                $this->project->addReference($this->referenceName, $e);
            }

            if (!empty($this->catchContainer)) {
                $this->catchContainer->perform();
            } else {
                $exc = $e;
            }
        }

        if (!empty($this->finallyContainer)) {
            $this->finallyContainer->perform();
        }

        if (!empty($exc)) {
            throw $exc;
        }
    }

    /**
     * Sets the name of the property that will
     * contain the exception message.
     *
     * @param string $property
     */
    public function setProperty($property)
    {
        $this->propertyName = (string)$property;
    }

    /**
     * Sets the name of the reference that will
     * contain the exception.
     *
     * @param Exception $reference
     *
     * @return void
     */
    public function setReference($reference)
    {
        $this->referenceName = $reference;
    }

    /**
     * Add nested <try> element
     *
     * @param Sequential $container
     */
    public function addTry(Sequential $container)
    {
        $this->tryContainer = $container;
    }

    /**
     * Add nested <catch> element
     *
     * @param Sequential $container
     */
    public function addCatch(Sequential $container)
    {
        $this->catchContainer = $container;
    }

    /**
     * Add nested <finally> element
     *
     * @param Sequential $container
     */
    public function addFinally(Sequential $container)
    {
        $this->finallyContainer = $container;
    }
}