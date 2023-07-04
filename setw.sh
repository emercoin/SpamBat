#!/bin/sh
W="wallet.dat"
test -L $W && test -e $1 && rm $W && ln -s $1 $W && echo "Relinked: $W -> $1"
