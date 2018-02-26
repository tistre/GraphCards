<?php

namespace AppBundle\Security;

use Symfony\Component\Security\Core\User\UserInterface;


class User implements UserInterface
{
    /** @var string */
    protected $username = 'anonymous';

    /** @var array */
    protected $oAuthInfo = [];


    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        return ['ROLE_USER'];
    }


    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return '';
    }


    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return null;
    }


    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->username;
    }


    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
    }


    /**
     * @param array $oAuthInfo
     */
    public function setOAuthInfo(array $oAuthInfo)
    {
        $this->oAuthInfo = $oAuthInfo;

        if (!empty($oAuthInfo['mail'])) {
            $this->username = $oAuthInfo['mail'];
        }
    }


    /**
     * @return array
     */
    public function getOAuthInfo()
    {
        return $this->oAuthInfo;
    }
}