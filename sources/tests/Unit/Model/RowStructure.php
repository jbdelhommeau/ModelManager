<?php
/*
 * This file is part of the PommProject/ModelManager package.
 *
 * (c) 2014 Grégoire HUBERT <hubert.greg@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace PommProject\ModelManager\Test\Unit\Model;

use PommProject\ModelManager\Model\RowStructure as PommRowStructure;
use Atoum;

class RowStructure extends Atoum
{
    public function testConstructor()
    {
        $this->exception(function() { new FailingRelationNameStructure(); })
            ->isinstanceof('\PommProject\ModelManager\Exception\ModelException')
            ->message->contains('relation name')
            ;

        $this->exception(function() { new FailingFieldDefinitionStructure(); })
            ->isInstanceOf('\PommProject\ModelManager\Exception\ModelException')
            ->message->contains('field definition')
            ;

        $structure = new GoodStructure();
        $this->object($structure)
            ->isInstanceOf('\PommProject\ModelManager\Model\RowStructure')
            ->isInstanceOf('\ArrayAccess')
            ;
    }

    public function testInherits()
    {
        $structure = new GoodStructure();
        $this->object($structure->inherits(new ChuStructure()))
            ->isInstanceOf('\PommProject\ModelManager\Model\RowStructure')
            ->array($structure->getDefinition())
            ->isIdenticalTo(['pika' => 'int4', 'chu' => 'bool'])
            ;
    }

    public function testAddField()
    {
        $structure = new GoodStructure();
        $this->array($structure->getDefinition())
            ->isIdenticalTo(['pika' => 'int4'])
            ->array($structure->addField('chu', 'bool')->getDefinition())
            ->isIdenticalTo(['pika' => 'int4', 'chu' => 'bool'])
            ->exception(function() use ($structure) { $structure->addField(null, 'int4'); })
            ->isinstanceof('\InvalidArgumentException')
            ->message->contains("'name' cannot be null")
            ->exception(function() use ($structure) { $structure->addField('name', null); })
            ->isinstanceof('\InvalidArgumentException')
            ->message->contains("'type' cannot be null")
            ;
    }

    public function testGetFieldNames()
    {
        $structure = new GoodStructure();
        $this->array($structure->getFieldNames())
            ->isIdenticalTo(['pika'])
            ->array($structure->addField('chu', 'bool')->getFieldNames())
            ->isIdenticalTo(['pika', 'chu'])
            ;
    }

    public function testHasField()
    {
        $structure = new GoodStructure();
        $this->boolean($structure->hasField('pika'))
            ->isTrue()
            ->boolean($structure->hasField('chu'))
            ->isFalse()
            ->boolean($structure->addField('chu', 'bool')->hasField('chu'))
            ->isTrue()
            ;
    }

    public function testGetTypeFor()
    {
        $structure = new GoodStructure();
        $this->string($structure->getTypeFor('pika'))
            ->isEqualTo('int4')
            ->exception(function() use ($structure) { $structure->getTypeFor(null); })
            ->isinstanceof('\InvalidArgumentException')
            ->message->contains("'name' cannot be null")
            ->exception(function() use ($structure) { $structure->getTypeFor('chu'); })
            ->isinstanceof('\PommProject\ModelManager\Exception\ModelException')
            ->message->contains("Field 'chu' is not defined")
            ->string($structure->addField('chu', 'bool')->getTypeFor('chu'))
            ->isEqualTo('bool')
            ;
    }

    public function testGetDefinition()
    {
        $structure = new GoodStructure();
        $this->array($structure->getDefinition())
            ->isIdenticalTo(['pika' => 'int4'])
            ->array($structure->addField('chu', 'bool')->getDefinition())
            ->isIdenticalTo(['pika' => 'int4', 'chu' => 'bool'])
            ;
    }

    public function testGetRelationName()
    {
        $structure = new GoodStructure();
        $this->string($structure->getRelationName())
            ->isEqualTo('pika')
            ;
    }

    public function testGetPrimaryKey()
    {
        $structure = new GoodStructure();
        $this->array($structure->getPrimaryKey())
            ->isEmpty()
            ;
        $structure = new ChuStructure();
        $this->array($structure->getPrimaryKey())
            ->isIdenticalTo(['chu'])
            ;
    }

    public function testArrayAccess()
    {
        $structure = new GoodStructure();
        $this->string($structure['pika'])
            ->isEqualTo('int4')
            ;
        $structure['chu'] = 'bool';
        $this->boolean(isset($structure['chu']))
            ->isTrue()
            ->exception(function() use ($structure) { unset($structure['chu']); })
            ->isInstanceOf('\PommProject\ModelManager\Exception\ModelException')
            ->message->contains('Cannot unset a structure field')
            ;
    }
}

class FailingFieldDefinitionStructure extends PommRowStructure
{
    protected function initialize()
    {
        $this->relation_name = 'plop';
    }
}

class FailingRelationNameStructure extends PommRowStructure
{
    protected function initialize()
    {
        $this->field_definitions['pika'] = 'int4';
    }
}

class GoodStructure extends PommRowStructure
{
    protected function initialize()
    {
        $this->relation_name = 'pika';
        $this->field_definitions['pika'] = 'int4';
    }
}

class ChuStructure extends PommRowStructure
{
    protected function initialize()
    {
        $this->relation_name = 'chu';
        $this->field_definitions['chu'] = 'bool';
        $this->primary_key = ['chu'];
    }
}