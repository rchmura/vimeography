<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class Vimeography_Helpers {
  /**
   * [apply_common_formatting description]
   * @param  [type] $data [description]
   * @return [type]       [description]
   */
  public function apply_common_formatting($data) {
    $items = array();

    foreach($data as $item) {
      // status can be one of the following:
      // 'available';'uploading''transcoding';'uploading_error';'transcoding_error';
      if ($item->status !== 'available') {
        continue;
      }

      $item->video_id = str_replace('/', '', strrchr($item->link, '/'));

      if ($item->duration AND ! strpos($item->duration, ':'))
        $item->duration = $this->seconds_to_minutes($item->duration);

      $item = $this->format_video_thumbnails($item);

      // Linkify any URLs in the description
      $item->description = $this->link_urls( nl2br($item->description) );

      $items[] = apply_filters('vimeography/edit-video/' . $item->video_id, $item);
    }

    return apply_filters('vimeography/edit-videos', $items);
  }

  /**
   * Sort the Vimeo thumbnails into different keys based on their index
   * in the returned pictures array from Vimeo
   *
   * @param  [type] $item [description]
   * @return [type]       [description]
   */
  public function format_video_thumbnails($item) {
    // Format the video thumbnails
    for ($i = 0; $i < count($item->pictures); $i++)
    {
      if ($item->pictures[$i]->type == 'thumbnail')
      {
        switch ($i)
        {
          case 0:
            $item->thumbnail_large = $item->pictures[$i]->link;
            break;
          case 1:
            $item->thumbnail_medium = $item->pictures[$i]->link;
            break;
          case 2:
            $item->thumbnail_small = $item->pictures[$i]->link;
            break;
          case count($item->pictures) - 1:
            $item->thumbnail_tiny = $item->pictures[$i]->link;
            break;
          default:
            break;
        }
      }
    }

    return $item;
  }

  /**
   * Converts the video's duration in seconds to the MM:SS format.
   *
   * @access public
   * @param mixed $seconds
   * @return void
   */
  public function seconds_to_minutes($seconds)
  {
    /// get minutes
    $minResult = floor($seconds/60);

    /// if minutes is between 0-9, add a "0" --> 00-09
    if($minResult < 10){$minResult = 0 . $minResult;}

    /// get sec
    // HT: Clark Bilorusky http://clarkbilorusky.com
    $secResult = floor(($seconds/60 - $minResult)*60);

    /// if secondes is between 0-9, add a "0" --> 00-09
    if($secResult < 10){$secResult = 0 . $secResult;}

    /// return result
    return $minResult . ":" . $secResult;
  }

  /**
   * [get_featured_embed description]
   * @param  [type] $link [description]
   * @return [type]       [description]
   */
  public function get_featured_embed($link)
  {
    $params = array(
      'url'       => $link,
      'autoplay'  => 0,
      'title'     => 0,
      'portrait'  => 0,
      'byline'    => 0,
      'api'       => 1,
      'player_id' => 'vimeography'. rand('1', '999999')
    );

    $query = http_build_query($params);

    $oembed = wp_remote_get( 'http://vimeo.com/api/oembed.json?' . $query);

    if ( is_wp_error( $oembed ) ) {
      throw new Vimeography_Exception( __('Vimeography could not retrieve the featured video: ', 'vimeography') . $oembed->get_error_message() );
    }
    else
    {
      switch( $oembed['response']['code'] ) {
        case 200:
          $oembed = json_decode($oembed['body']);
          $oembed->html = str_replace('<iframe', '<iframe id="' . $params['player_id'] . '"', $oembed->html);
          return $oembed->html;
        case 403:
          throw new Vimeography_Exception( __('Your video privacy settings for must be adjusted to allow displaying this video on your site.', 'vimeography') );
        default:
          break;
      }
    }
  }

  /**
   * Truncate strings to defined limit.
   * Original PHP code by Chirp Internet: www.chirp.com.au
   *
   * @access public
   * @param mixed $string
   * @param mixed $limit
   * @param string $break (default: " ")
   * @param string $pad (default: "...")
   * @return void
   */
  public function truncate($string, $limit, $break = ' ', $pad = '...')
  {
    // return with no change if string is shorter than $limit
    if (strlen($string) <= $limit)
      return $string;

    $string = substr($string, 0, $limit);

    if (false !== ($breakpoint = strrpos($string, $break)))
    {
      $string = substr($string, 0, $breakpoint);
    }

    return $string . $pad;
  }

  /**
   * Restore HTML tags to truncated strings.
   * Original PHP code by Chirp Internet: www.chirp.com.au
   *
   * @access public
   * @param mixed $input
   * @return void
   */
  public function restore_tags($input)
  {
    $opened = array();
    // loop through opened and closed tags in order
    if(preg_match_all("/<(\/?[a-z]+)>?/i", $input, $matches))
    {
      foreach($matches[1] as $tag)
      {
        if(preg_match("/^[a-z]+$/i", $tag, $regs))
        {
          // a tag has been opened
          if(strtolower($regs[0]) != 'br') $opened[] = $regs[0];
        }
        elseif(preg_match("/^\/([a-z]+)$/i", $tag, $regs))
        {
          // a tag has been closed
          unset($opened[array_pop(array_keys($opened, $regs[1]))]);
        }
      }
    }
    // close tags that are still open
    if($opened)
    {
      $tagstoclose = array_reverse($opened);
      foreach($tagstoclose as $tag) $input .= "</$tag>";
    }
    return $input;
  }

  /**
   *  UrlLinker - facilitates turning plain text URLs into HTML links.
   *
   *  Author: SÃ¸ren LÃ¸vborg
   *
   *  To the extent possible under law, SÃ¸ren LÃ¸vborg has waived all copyright
   *  and related or neighboring rights to UrlLinker.
   *  http://creativecommons.org/publicdomain/zero/1.0/
   *
   *  Transforms plain text into valid HTML, escaping special characters and
   *  turning URLs into links.
   *
   *  Can be used in any Vimeography theme file. EG:
   *  $item->description = $helpers->link_urls($item->description);
   */
  public function link_urls($text)
  {
    /*
     *  Regular expression bits used by link_urls() to match URLs.
     */
    $rexScheme    = 'https?://';
    // $rexScheme    = "$rexScheme|ftp://"; // Uncomment this line to allow FTP addresses.
    $rexDomain    = '(?:[-a-zA-Z0-9]{1,63}\.)+[a-zA-Z][-a-zA-Z0-9]{1,62}';
    $rexIp        = '(?:[1-9][0-9]{0,2}\.|0\.){3}(?:[1-9][0-9]{0,2}|0)';
    $rexPort      = '(:[0-9]{1,5})?';
    $rexPath      = '(/[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]*?)?';
    $rexQuery     = '(\?[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
    $rexFragment  = '(#[!$-/0-9:;=@_\':;!a-zA-Z\x7f-\xff]+?)?';
    $rexUsername  = '[^]\\\\\x00-\x20\"(),:-<>[\x7f-\xff]{1,64}';
    $rexPassword  = $rexUsername; // allow the same characters as in the username
    $rexUrl       = "($rexScheme)?(?:($rexUsername)(:$rexPassword)?@)?($rexDomain|$rexIp)($rexPort$rexPath$rexQuery$rexFragment)";
    $rexTrailPunct= "[)'?.!,;:]"; // valid URL characters which are not part of the URL if they appear at the very end
    $rexNonUrl    = "[^-_$+.!*'(),;/?:@=&a-zA-Z0-9]"; // characters that should never appear in a URL
    $rexUrlLinker = "{\\b$rexUrl(?=$rexTrailPunct*($rexNonUrl|$))}";
    // $rexUrlLinker .= 'i'; // Uncomment this line to allow uppercase URL schemes (e.g. "HTTP://google.com").

    /**
     *  $validTlds is an associative array mapping valid TLDs to the value true.
     *  Since the set of valid TLDs is not static, this array should be updated
     *  from time to time.
     *
     *  List source:  http://data.iana.org/TLD/tlds-alpha-by-domain.txt
     *  Last updated: 2012-09-06
     */
    $validTlds = array_fill_keys(explode(" ", ".ac .ad .ae .aero .af .ag .ai .al .am .an .ao .aq .ar .arpa .as .asia .at .au .aw .ax .az .ba .bb .bd .be .bf .bg .bh .bi .biz .bj .bm .bn .bo .br .bs .bt .bv .bw .by .bz .ca .cat .cc .cd .cf .cg .ch .ci .ck .cl .cm .cn .co .com .coop .cr .cu .cv .cw .cx .cy .cz .de .dj .dk .dm .do .dz .ec .edu .ee .eg .er .es .et .eu .fi .fj .fk .fm .fo .fr .ga .gb .gd .ge .gf .gg .gh .gi .gl .gm .gn .gov .gp .gq .gr .gs .gt .gu .gw .gy .hk .hm .hn .hr .ht .hu .id .ie .il .im .in .info .int .io .iq .ir .is .it .je .jm .jo .jobs .jp .ke .kg .kh .ki .km .kn .kp .kr .kw .ky .kz .la .lb .lc .li .lk .lr .ls .lt .lu .lv .ly .ma .mc .md .me .mg .mh .mil .mk .ml .mm .mn .mo .mobi .mp .mq .mr .ms .mt .mu .museum .mv .mw .mx .my .mz .na .name .nc .ne .net .nf .ng .ni .nl .no .np .nr .nu .nz .om .org .pa .pe .pf .pg .ph .pk .pl .pm .pn .post .pr .pro .ps .pt .pw .py .qa .re .ro .rs .ru .rw .sa .sb .sc .sd .se .sg .sh .si .sj .sk .sl .sm .sn .so .sr .st .su .sv .sx .sy .sz .tc .td .tel .tf .tg .th .tj .tk .tl .tm .tn .to .tp .tr .travel .tt .tv .tw .tz .ua .ug .uk .us .uy .uz .va .vc .ve .vg .vi .vn .vu .wf .ws .xn--0zwm56d .xn--11b5bs3a9aj6g .xn--3e0b707e .xn--45brj9c .xn--80akhbyknj4f .xn--80ao21a .xn--90a3ac .xn--9t4b11yi5a .xn--clchc0ea0b2g2a9gcd .xn--deba0ad .xn--fiqs8s .xn--fiqz9s .xn--fpcrj9c3d .xn--fzc2c9e2c .xn--g6w251d .xn--gecrj9c .xn--h2brj9c .xn--hgbk6aj7f53bba .xn--hlcj6aya9esc7a .xn--j6w193g .xn--jxalpdlp .xn--kgbechtv .xn--kprw13d .xn--kpry57d .xn--lgbbat1ad8j .xn--mgb9awbf .xn--mgbaam7a8h .xn--mgbayh7gpa .xn--mgbbh1a71e .xn--mgbc0a9azcg .xn--mgberp4a5d4ar .xn--o3cw4h .xn--ogbpf8fl .xn--p1ai .xn--pgbs0dh .xn--s9brj9c .xn--wgbh1c .xn--wgbl6a .xn--xkc2al3hye2a .xn--xkc2dl3a5ee0h .xn--yfro4i67o .xn--ygbi2ammx .xn--zckzah .xxx .ye .yt .za .zm .zw"), true);

    $html = '';

    $position = 0;
    while (preg_match($rexUrlLinker, $text, $match, PREG_OFFSET_CAPTURE, $position))
    {
      list($url, $urlPosition) = $match[0];

      // Add the text leading up to the URL.
      $html .= substr($text, $position, $urlPosition - $position);

      $scheme      = $match[1][0];
      $username    = $match[2][0];
      $password    = $match[3][0];
      $domain      = $match[4][0];
      $afterDomain = $match[5][0]; // everything following the domain
      $port        = $match[6][0];
      $path        = $match[7][0];

      // Check that the TLD is valid or that $domain is an IP address.
      $tld = strtolower(strrchr($domain, '.'));
      if (preg_match('{^\.[0-9]{1,3}$}', $tld) || isset($validTlds[$tld]))
      {
        // Do not permit implicit scheme if a password is specified, as
        // this causes too many errors (e.g. "my email:foo@example.org").
        if (!$scheme && $password)
        {
          $html .= htmlspecialchars($username);

          // Continue text parsing at the ':' following the "username".
          $position = $urlPosition + strlen($username);
          continue;
        }

        if (!$scheme && $username && !$password && !$afterDomain)
        {
          // Looks like an email address.
          $completeUrl = "mailto:$url";
          $linkText = $url;
        }
        else
        {
          // Prepend http:// if no scheme is specified
          $completeUrl = $scheme ? $url : "http://$url";
          $linkText = "$domain$port$path";
        }

        $linkHtml = '<a href="' . htmlspecialchars($completeUrl) . '" target="_blank">'
          . htmlspecialchars($linkText)
          . '</a>';

        // Cheap e-mail obfuscation to trick the dumbest mail harvesters.
        $linkHtml = str_replace('@', '&#64;', $linkHtml);

        // Add the hyperlink.
        $html .= $linkHtml;
      }
      else
      {
        // Not a valid URL.
        $html .= htmlspecialchars($url);
      }

      // Continue text parsing from after the URL.
      $position = $urlPosition + strlen($url);
    }

    // Add the remainder of the text.
    $html .= substr($text, $position);
    return $html;
  }

}
