<?php 
function getDN($ad, $samaccountname, $basedn)
{
  $result = ldap_search($ad, $basedn, "(sAMAccountName={$samaccountname})", array(
    'dn'
  )) or die(ldap_error($ad));
  if (! $result)
  {
    return '';
  }
 
  $entries = ldap_get_entries($ad, $result);
  if ($entries['count'] > 0)
  {
    return $entries[0]['dn'];
    //return $entries;
  }
 
  return '';
}
 
/**
 * This function retrieves and returns Common Name from a given Distinguished
 * Name.
 *
 * @param string $dn
 *          The Distinguished Name.
 * @return string The Common Name.
 */
function getCN($dn)
{
  preg_match('/[^,]*/', $dn, $matchs, PREG_OFFSET_CAPTURE, 3);
  return $matchs[0][0];
  //return $matchs;
}
 
/**
 * This function checks group membership of the user, searching only in
 * specified group (not recursively).
 *
 * @param resource $ad
 *          An LDAP link identifier, returned by ldap_connect().
 * @param string $userdn
 *          The user Distinguished Name.
 * @param string $groupdn
 *          The group Distinguished Name.
 * @return boolean Return true if user is a member of group, and false if not
 *         a member.
 */
function checkGroup($ad, $userdn, $groupdn)
{
  $result = ldap_read($ad, $userdn, "(memberof={$groupdn})", array(
    'members'
  ));
  if (! $result)
  {
    return false;
  }
 
  $entries = ldap_get_entries($ad, $result);
 
  return ($entries['count'] > 0);
}
 
/**
 * This function checks group membership of the user, searching in specified
 * group and groups which is its members (recursively).
 *
 * @param resource $ad
 *          An LDAP link identifier, returned by ldap_connect().
 * @param string $userdn
 *          The user Distinguished Name.
 * @param string $groupdn
 *          The group Distinguished Name.
 * @return boolean Return true if user is a member of group, and false if not
 *         a member.
 */
function checkGroupEx($ad, $userdn, $groupdn)
{
  $result = ldap_read($ad, $userdn, '(objectclass=*)', array(
    'memberof'
  ));
  if (! $result)
  {
    return false;
  }
 
  $entries = ldap_get_entries($ad, $result);
  if ($entries['count'] <= 0)
  {
    return false;
  }
 
  if (empty($entries[0]['memberof']))
  {
    return false;
  }
 
  for ($i = 0; $i < $entries[0]['memberof']['count']; $i ++)
  {
    if ($entries[0]['memberof'][$i] == $groupdn)
    {
      return true;
    }
    elseif (checkGroupEx($ad, $entries[0]['memberof'][$i], $groupdn))
    {
      return true;
    }
  }
 
  return false;
}

//this is my own addition, to get mail and department
function getDetails($ad, $samaccountname, $basedn)
{
  $result = ldap_search($ad, $basedn, "(sAMAccountName={$samaccountname})", array(
    'dn','mail','department'
  )) or die(ldap_error($ad));
  if (! $result)
  {
    return '';
  }
 
  $entries = ldap_get_entries($ad, $result);
  if ($entries['count'] > 0)
  {
    $response = array();
    $response['mail'] = $entries[0]['mail'];
    $response['department'] = $entries[0]['department'];
    //return $entries[0]['dn'];
    return $response;
  }
 
  return '';
}
?>