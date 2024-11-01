#!/bin/bash
cd -- "$( dirname -- "${BASH_SOURCE[0]}" )" &> /dev/null && pwd
while :; do php -f events.php; done