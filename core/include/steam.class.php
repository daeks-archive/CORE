<?php
  
class steam
{
  private static $profilapi = 'http://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/';
  private static $banapi = 'https://api.steampowered.com/ISteamUser/GetPlayerBans/v1/';
  private static $libapi = 'http://api.steampowered.com/IPlayerService/GetOwnedGames/v0001/';
  private static $playedapi = 'http://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/';
  
  public static function getuser($id)
  {
    if (defined('STEAM_APIKEY')) {
      $content = json_decode(cache::write('user_'.$id, steam::$profilapi.'?key='.STEAM_APIKEY.'&steamids='.$id, true), true);
      if (isset($content['response']['players'][0])) {
        return $content['response']['players'][0];
      } else {
        return array();
      }
    } else {
      return array();
    }
  }
  
  public static function getbans($id)
  {
    if (defined('STEAM_APIKEY')) {
      $content = json_decode(cache::write('bans_'.$id, steam::$banapi.'?key='.STEAM_APIKEY.'&steamids='.$id, true), true);
      if (isset($content['players'][0])) {
        return $content['players'][0];
      } else {
        return array();
      }
    } else {
      return array();
    }
  }
  
  public static function getplayed($id)
  {
    if (defined('STEAM_APIKEY')) {
      $content = json_decode(cache::write('played_'.$id, steam::$playedapi.'?key='.STEAM_APIKEY.'&steamid='.$id, true), true);
      if (isset($content['response']['games'])) {
        return $content['response']['games'];
      } else {
        return array();
      }
    } else {
      return array();
    }
  }
  
  public static function getowned($id)
  {
    if (defined('STEAM_APIKEY')) {
      $content = json_decode(cache::write('owned_'.$id, steam::$libapi.'?key='.STEAM_APIKEY.'&steamid='.$id, true), true);
      if (isset($content['response']['games'])) {
        return $content['response']['games'];
      } else {
        return array();
      }
    } else {
      return array();
    }
  }
  
  public static function getplaytime($id, $appid)
  {
    if (defined('STEAM_APIKEY')) {
      $content = json_decode(cache::write('playtime_'.$id, steam::$libapi.'?key='.STEAM_APIKEY.'&steamid='.$id, true), true);
      if (isset($content['response']['games'])) {
        $index = array_search($appid, array_column($content['response']['games'], 'appid'));
        if ($index >= 0) {
          return $content['response']['games'][$index]['playtime_forever'];
        } else {
          return -1;
        }
      } else {
        return -1;
      }
    } else {
      return array();
    }
  }
}
  
?>