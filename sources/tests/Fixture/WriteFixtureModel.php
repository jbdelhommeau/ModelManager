<?php
/*
 * This file is part of the PommProject/ModelManager package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\ModelManager\Test\Fixture;

use PommProject\Foundation\Session;
use PommProject\Foundation\Exception\SqlException;
use PommProject\ModelManager\Test\Fixture\SimpleFixtureModel;
use PommProject\ModelManager\Model\ModelTrait\WriteTrait;

class WriteFixtureModel extends SimpleFixtureModel
{
    use WriteTrait;

    public function getRelation()
    {
        return 'write_fixture';
    }

    public function initialize(Session $session)
    {
        parent::initialize($session);
        $this->executeAnonymousQuery(
            sprintf(
                "create temporary table %s (id serial primary key, a_varchar varchar, a_boolean boolean)",
                $this->getRelation()
            )
        );

        $this->primary_key = ['id'];
    }

    public function shutdown()
    {
        $this->executeAnonymousQuery(sprintf("drop table %s", $this->getRelation()));
    }
}