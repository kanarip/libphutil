<?php

/**
 * A JSON parser.
 *
 * @phutil-external-symbol class Seld\JsonLint\JsonParser
 * @phutil-external-symbol class Seld\JsonLint\ParsingException
 */
final class PhutilJSONParser extends Phobject {

  private $allowDuplicateKeys = false;

  public function setAllowDuplicateKeys($allow_duplicate_keys) {
    $this->allowDuplicateKeys = $allow_duplicate_keys;
    return $this;
  }

  public function parse($json) {
    require_once 'Seld/JsonLint/autoload.php';

    $parser = new Seld\JsonLint\JsonParser();
    try {
      $output = $parser->parse($json, $this->getFlags());
    } catch (Seld\JsonLint\ParsingException $ex) {
      $details = $ex->getDetails();
      $message = preg_replace("/^Parse error .*\\^\n/s", '', $ex->getMessage());

      throw new PhutilJSONParserException(
          $message,
          idx(idx($details, 'loc', array()), 'last_line'),
          idx(idx($details, 'loc', array()), 'last_column'),
          idx($details, 'token'),
          idx($details, 'expected'));
    }

    if (!is_array($output)) {
      throw new PhutilJSONParserException(
        pht(
          '%s is not a valid JSON object.',
          PhutilReadableSerializer::printShort($json)));
    }

    return $output;
  }

  private function getFlags() {
    $flags = Seld\JsonLint\JsonParser::PARSE_TO_ASSOC;

    if ($this->allowDuplicateKeys) {
      $flags |= Seld\JsonLint\JsonParser::ALLOW_DUPLICATE_KEYS;
    } else {
      $flags |= Seld\JsonLint\JsonParser::DETECT_KEY_CONFLICTS;
    }

    return $flags;
  }

}
