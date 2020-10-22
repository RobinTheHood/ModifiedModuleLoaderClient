<?php

/*
 * This file is part of MMLC - ModifiedModuleLoaderClient.
 *
 * (c) Robin Wieschendorf <mail@robinwieschendorf.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace RobinTheHood\ModifiedModuleLoaderClient\GraphQl;

use RobinTheHood\Tokenizer\Tokenizer;

class QueryParser
{
    private $tokenPointer = 0;
    private $tokens = [];

    public function parse($queryString)
    {
        $tokenizer = new Tokenizer('', __DIR__ . '/GraphQlTokens.php');
        $tokenizer->setContent($queryString);
        $this->tokens = $tokenizer->getAllTokens();

        $query = $this->expectsQuery();

        return $query;
    }

    public function expectsQuery()
    {
        $query = [];

        if (!$this->expectsToken('T_OPEN_{')) {
            return false;
        }

        if ($function = $this->expectsFunction()) {
            $query['action'] = $function;
        } else {
            return false;
        }

        if (!$this->expectsToken('T_CLOSE_}')) {
            return false;
        }

        return $query;
    }

    public function expectsFunction()
    {
        $function = [];

        if ($token = $this->expectsToken('T_IDENT')) {
            if (substr($token->value, 0, 3) == 'all') {
                $method = 'getAll';
                $obj = substr($token->value, 3, -1);
            } else {
                $method = 'get';
                $obj = $token->value;
            }

            $function = [
                'method' => $method,
                'obj' => $obj
            ];
        } else {
            return false;
        }

        if ($conditions = $this->expectsConditions()) {
            $function['conditions'] = $conditions;
        }

        if ($variables = $this->expectsVariables()) {
            $function['variables'] = $variables;
        }

        return $function;
    }

    public function expectsConditions()
    {
        $conditions = [];
        //$condition = [];

        if (!$this->expectsToken('T_OPEN_(')) {
            return false;
        }

        while ($condition = $this->expectsCondition()) {
            $conditions[] = $condition;
        }
        // if($token = $this->expectsToken('T_IDENT')) {
        //     $condition['name'] = $token->value;
        // }
        //
        // if(!$token = $this->expectsToken('T_ASSIGN')) {
        //     return false;
        // }
        //
        // if(!$token = $this->expectsToken('T_STRING_START')) {
        //     return false;
        // }
        //
        // if($token = $this->expectsToken('T_STRING')) {
        //     $condition['value'] = $token->value;
        // }
        //
        // if(!$token = $this->expectsToken('T_STRING_END')) {
        //     return false;
        // }

        if (!$this->expectsToken('T_CLOSE_)')) {
            return false;
        }

        //$conditions[] = $condition;
        return $conditions;
    }

    public function expectsCondition()
    {
        $condition = [];

        if ($token = $this->expectsToken('T_IDENT')) {
            $condition['name'] = $token->value;
        }

        if (!$token = $this->expectsToken('T_ASSIGN')) {
            return false;
        }

        if (!$token = $this->expectsToken('T_STRING_START')) {
            return false;
        }

        if ($token = $this->expectsToken('T_STRING')) {
            $condition['value'] = $token->value;
        }

        if (!$token = $this->expectsToken('T_STRING_END')) {
            return false;
        }

        return $condition;
    }

    public function expectsVariables()
    {
        $variables = [];

        if (!$this->expectsToken('T_OPEN_{')) {
            return false;
        }

        while ($token = $this->expectsToken('T_IDENT')) {
            $variables[] = [
                'name' => $token->value
            ];
        }

        if (!$this->expectsToken('T_CLOSE_}')) {
            return false;
        }

        return $variables;
    }


    public function expectsToken($tokenType)
    {
        $token = $this->getNextKnownToken();

        if ($token && $token->type == $tokenType) {
            return $token;
        }
        $this->tokenPointer--;
        return false;
    }

    public function getNextKnownToken()
    {
        $token = $this->getNextToken();
        while (!$this->isEndOfTokens() && $token->type == 'T_UNKNOWN') {
            $token = $this->getNextToken();
        }
        return $token;
    }

    public function getNextToken()
    {
        $index = $this->tokenPointer;
        $value = empty($this->tokens[$index]) ? null : $this->tokens[$index];
        $this->tokenPointer++;
        return $value;
    }

    public function isEndOfTokens()
    {
        if ($this->tokenPointer >= count($this->tokens)) {
            return true;
        }

        return false;
    }
}
