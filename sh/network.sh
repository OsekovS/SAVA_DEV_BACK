#!/bin/bash
sleep 2
ifconfig ens32 $0
route add default gw $1 ens32
