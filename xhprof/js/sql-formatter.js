var SqlFormatter = (function() {

    function flatten(input) {
        var lex = new Lexer(),
            result = '';

        lex.addRule(/\s+/, function() {
            result += ' ';
        });

        lex.addRule(/\S+/, function(lexeme) {
            result += lexeme;
        });

        lex.setInput(input).lex();

        return result;
    }

    function SqlFormatter(input) {
        this.lexer = null;
        this.input = input;
        this.result = '';
        this.indentDepth = 0;
        this.stack = {
            stack: [],

            push: function() {
                this.stack.stack.push([this.lexer.state, this.indentDepth]);
            }.bind(this),

            pop: function() {
                var state = this.stack.stack.pop();
                this.lexer.state = state[0];
                this.indentDepth = state[1];
            }.bind(this)
        };

        this.prepareLexer();
    }

    SqlFormatter.prototype = {

        INITIAL_STATE: 0,

        DEFAULT_STATE: 1,

        STRING_LITERAL_STATE: 2,

        LEVEL_1_KEYWORDS: [
            'SELECT( DISTINCT)?', 'FROM', 'ORDER BY', 'LIMIT', '((LEFT|INNER|RIGHT) )?JOIN',
            'WHERE', 'UPDATE', 'SET', 'INSERT INTO', 'VALUES'
        ],

        prepareLexer: function() {
            var self = this;
            this.lexer = new Lexer();

            var level1KeyworRegexp = new RegExp(self.LEVEL_1_KEYWORDS.map(function(kw) {
                return kw + '\\s';
            }).join('|'), 'i');

            this.lexer.addRule(level1KeyworRegexp, function(lexeme) {
                if (self.isInState(self.DEFAULT_STATE)) {
                    self.indentDepth--;
                    self.result += '\n';
                    self.result += self.indent();
                } else {
                    self.setState(self.DEFAULT_STATE);
                }

                self.result += lexeme + '\n';
                self.indentDepth++;
                self.result += self.indent();

            }, [this.INITIAL_STATE, this.DEFAULT_STATE]);

            this.lexer.addRule(/,\s?/, function(lexeme) {
                self.result += lexeme + '\n' + self.indent();
            }, [this.INITIAL_STATE, this.DEFAULT_STATE]);

            this.lexer.addRule(/\son\s|\sand\s|\sor\s/i, function(lexeme) {
                self.result += '\n' + self.indent() + lexeme.trim() + ' ';
            }, [this.INITIAL_STATE, this.DEFAULT_STATE]);

            this.lexer.addRule(/\(\s?/, function(lexeme) {
                self.result += lexeme.trim() + '\n';
                self.stack.push();
                self.indentDepth++;
                self.result += self.indent();
                self.setState(self.INITIAL_STATE);
            }, [this.INITIAL_STATE, this.DEFAULT_STATE]);

            this.lexer.addRule(/\)/, function(lexeme) {
                self.result += '\n';
                self.stack.pop();
                self.result += self.indent() + lexeme;
            }, [this.INITIAL_STATE, this.DEFAULT_STATE]);

            this.lexer.addRule(/'/, function(lexeme) {
                self.stack.push();
                self.setState(self.STRING_LITERAL_STATE);
                self.result += lexeme;
            }, [this.INITIAL_STATE, this.DEFAULT_STATE]);

            this.lexer.addRule(/'/, function(lexeme) {
                self.result += lexeme;
                self.stack.pop();
            }, [self.STRING_LITERAL_STATE]);

            this.lexer.addRule(/[\s\S]/, function(lexeme) {
                self.result += lexeme;
            }, [this.INITIAL_STATE, this.DEFAULT_STATE, self.STRING_LITERAL_STATE]);
        },

        indent: function() {
            return '    '.repeat(this.indentDepth)
        },

        setState: function(state) {
            this.lexer.state = state;
        },

        isInState: function(state) {
            return this.lexer.state == state;
        },

        format: function() {
            this.input = flatten(this.input);
            this.lexer.setInput(this.input).lex();
            return this.result;
        }
    };

    return SqlFormatter;
})();
