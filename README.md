# airline_food

* Implements https://esolangs.org/wiki/Airline_Food

# Dev notes

* So this is interesting - the parsing is pretty straightforward, since we don't really have any nested syntax to (and so real AST) to worry about.
* Adding to above, there is some need to keep track of structure - when doing the conditional jumps ("So..." and "Moving on...") - but nothing too complex.
