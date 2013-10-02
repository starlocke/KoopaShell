#!/bin/bash
START_TIME=$SECONDS
VERBOSE=0
EXE=`basename "$BASH_SOURCE"`
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
usage()
{
  echo "usage: $EXE [OPTIONS]"
  echo "options:"
  # Custom options are the first block shown
  echo "  -x PARAM  An example option that requires a parameter"
  # Common options are the last black shown
  echo ""
  echo "  -h        Shows this message and quits"
  echo "  -v        Raises verbose level (repeat for more; max TBD)"
  exit 1
}
while getopts ":hvx:" opt
do
  case $opt in
    # Basic -h and -v
    h ) usage;;
    v ) let VERBOSE=VERBOSE+1;;
    # Custom handlers
    x ) echo "I am option -x. I was given \"$OPTARG\".";;
    # Error-handlers
    \?) echo "Invalid option: -$OPTARG" >&2; exit 1;;
    : ) echo "Option -$OPTARG requires an argument." >&2; exit 1;;
  esac
done

# Script body ##########################################################

sudo ln -sf "$DIR/smask" /usr/local/bin/smask
sudo ln -sf "$DIR/chturf" /usr/local/bin/chturf
sudo ln -sf "$DIR/grep!" /usr/local/bin/grep!
sudo ln -sf "$DIR/sshfingerprint" /usr/local/bin/sshfingerprint

# Script body ##########################################################

ELAPSED_TIME=$(($SECONDS - $START_TIME))
if [ $VERBOSE -gt 0 ]; then echo "$EXE finished executing in $(($ELAPSED_TIME/60)) min, $(($ELAPSED_TIME%60)) sec"; fi
