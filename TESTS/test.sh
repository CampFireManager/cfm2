#! /bin/bash
BASE_DIR="`dirname $0`"
GENDOCS=true
if [ "$1" == "true" ]; then
  GENDOCS=true
fi
if [ -z $2 ]; then
  echo "Checking syntax... "
  rm -Rf /tmp/phpcs_stats "$BASE_DIR/../DOCS/" /tmp/testing_status
  mkdir "$BASE_DIR/../DOCS"
  find "$BASE_DIR/.." -name "*.php" -not -wholename "*/ExternalLibraries/*/*" -not -wholename "*/Templates/C*" -exec "$0" "$1" '{}' \;
  echo "Done."
  echo "Checking classes... "
  php "$BASE_DIR/missing_function_finder.php" "$BASE_DIR/../CLASSES" >> /tmp/phpcs_stats
  if [ ! -s /tmp/phpcs_stats ]; then
    if [ "$GENDOCS" == "true" ]; then
      echo -n "Generating Documentation... "
      `which phpdoc` -i */ExternalLibraries/*/*,*/Templates/C* -o HTML:frames:earthli -d "$BASE_DIR/.." -t "$BASE_DIR/../DOCS" > /dev/null
    fi
    echo "Done."
  fi
  if [ -s /tmp/phpcs_stats ]; then
    mv /tmp/phpcs_stats "$BASE_DIR/../DOCS/phpcs_failures.txt"
    less "$BASE_DIR/../DOCS/phpcs_failures.txt"
  fi
else
  rm /tmp/phpcs_test
  echo "Checking $2:"
  `which php` -l "$2" >/dev/null 2> /tmp/phpcs_test
  if [ $? == 0 ]; then
    echo "  PHP syntax OK"
    phpcs --standard=/usr/share/php/test/PHP_CodeSniffer/CodeSniffer/Standards/CFM2 "$2" > /tmp/phpcs_test
    if [ $? == 0 ]; then
      echo "  PHP_CodeSniffer OK"
    else
      echo "====================== PHPCS for $2 ==================" >>/tmp/phpcs_stats
      cat /tmp/phpcs_test >>/tmp/phpcs_stats
      echo "  PHP_CodeSniffer Failed"
    fi
  else
    echo "======================= PHP for $2 ===================" >>/tmp/phpcs_stats
    cat /tmp/phpcs_test >>/tmp/phpcs_stats
    echo "  PHP syntax Failed"
  fi
fi
