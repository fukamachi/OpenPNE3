<?php

/**
 * This file is part of the OpenPNE package.
 * (c) OpenPNE Project (http://www.openpne.jp/)
 *
 * For the full copyright and license information, please view the LICENSE
 * file and the NOTICE file that were distributed with this source code.
 */

class Community extends BaseCommunity
{
  public function getImageFileName()
  {
    if ($this->getFile())
    {
      return $this->getFile()->getName();
    }
    return '';
  }

  public function getConfigs()
  {
    $configs = sfConfig::get('openpne_community_config');

    $myConfigs = CommunityConfigPeer::retrievesByCommunityId($this->getId());

    $result = array();

    // initialize
    foreach ($configs as $key => $config)
    {
      $result[$config['Caption']] = '';
      if (isset($config[$key]['Default']))
      {
        $result[$config['Caption']] = $config[$key]['Default'];
      }
    }
    
    // set my configure
    foreach ($myConfigs as $myConfig)
    {
      $name = $myConfig->getName();
      if (isset($configs[$name]))
      {
        switch ($configs[$name]['FormType'])
        {
          case 'checkbox' :
          // FIXME
          case 'radio' :
          case 'select' :
            $value = $myConfig->getValue();
            if (isset($configs[$name]['Choices'][$value]))
            {
              $i18n = sfContext::getInstance()->getI18N();
              $result[$configs[$name]['Caption']] = $i18n->__($configs[$name]['Choices'][$value]);
            }
            break;
          default :
            $result[$configs[$name]['Caption']] = $myConfig->getValue();
        }
        $configs[$myConfig->getName()] = $myConfig->getValue();
      }
    }

    return $result;
  }

  public function getConfig($configName)
  {
    $config = CommunityConfigPeer::retrieveByNameAndCommunityId($configName, $this->getId());

    if (!$config)
    {
      return null;
    }

    return $config->getValue();
  }

  public function getMembers($limit = null, Criteria $c = null)
  {
    if (!$c)
    {
      $c = new Criteria();
    }
    if (!is_null($limit)) {
      $c->setLimit($limit);
    }
    $c->add(CommunityMemberPeer::COMMUNITY_ID, $this->getId());
    $c->add(CommunityMemberPeer::POSITION, 'pre', Criteria::NOT_EQUAL);
    $c->addJoin(MemberPeer::ID, CommunityMemberPeer::MEMBER_ID);
    return MemberPeer::doSelect($c);
  }

  public function getAdminMember()
  {
    return CommunityMemberPeer::getCommunityAdmin($this->getId())->getMember();
  }

  public function checkPrivilegeBelong($memberId)
  {
    if (!$this->isPrivilegeBelong($memberId))
    {
      throw new opPrivilegeException('fail');
    }
  }

  public function isPrivilegeBelong($memberId)
  {
    $c = new Criteria();
    $c->add(CommunityMemberPeer::MEMBER_ID, $memberId);
    $c->add(CommunityMemberPeer::POSITION, 'pre', Criteria::NOT_EQUAL);

    return (bool)$this->getCommunityMembers($c);
  }

  public function isAdmin($memberId)
  {
    $c = new Criteria();
    $c->add(CommunityMemberPeer::MEMBER_ID, $memberId);
    $c->add(CommunityMemberPeer::POSITION, 'admin');

    return (bool)$this->getCommunityMembers($c);
  }

  public function countCommunityMembers(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
  {
    if (is_null($criteria))
    {
      $criteria = new Criteria();
    }
    $criteria->add(MemberPeer::IS_ACTIVE, true);
    $criteria->add(CommunityMemberPeer::POSITION, 'pre', Criteria::NOT_EQUAL);
    $criteria->addJoin(CommunityMemberPeer::MEMBER_ID, MemberPeer::ID);
    return parent::countCommunityMembers($criteria, $distinct, $con);
  }

  public function getNameAndCount($format = '%s (%d)')
  {
    return sprintf($format, $this->getName(), $this->countCommunityMembers());
  }

  public function getRegisterPoricy()
  {
    $register_poricy = $this->getConfig('register_poricy');
    if ('open' === $register_poricy)
    {
      return 'Everyone can join';
    }
    else if ('close' === $register_poricy)
    {
      return 'Community\'s admin authorization needed';
    }
  }
}

