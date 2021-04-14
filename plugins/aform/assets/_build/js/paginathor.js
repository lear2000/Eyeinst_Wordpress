/*
	https://carldanley.com/js-module-pattern/
*/
var Paginathor = ( function( window, undefined ) {
	
	/*****************************************/	
	/** GLOBALS *****************************/
	/***************************************/	
	
	// this object is used to store private variables and methods across multiple instantiations
  
	var privates = {};

	var renderTemplate = function( t , f , a ){ 

		var TEMPLATES = t.templates;

		TEMPLATES = (f) ? TEMPLATES[f] : TEMPLATES;
		
		return TEMPLATES.call( t , a );

	};
	function Paginathor(_S_) {
	/*****************************************/	
	/** PRIVATE *****************************/
	/***************************************/	

	/*****************************************/	
	/** PUBLIC ******************************/
	/***************************************/	
	
	/***** 
	** DEFAULTS : Variables
	*****/	
	this._settings = { page : 1 , total : 0 , limit : 10 , adj : 1 , target : '#' , sep : '...' , elClass : 'pagi-item' , elData : ''};
	/*
		-- 	this._settings -> elClass : item-pagi
			'elClass' sets the class="" and the data attribute for the clickable element
			
			based on above 'elClass' : class="item-pagi" , data-item-pagi=

		--  this._settings -> elData : if you want to set a different data attribute
	*/
	this._S_ = _S_||{}; 

	this.templates = {
		/* example : function(MUST_BE_ARRAY) */
		isSelected : function(a){
			return "<li class=\"selected\"><a>"+a[0]+"</a></li>";
		},
		pageItem : function(a){

			var s  = this.getSettings();
			
			return "<li><a class=\""+s.elClass+"\" data-"+ ( ( s.elData ) ? s.elData : s.elClass ) +"=\""+a[1]+"\" href=\""+a[0]+a[1]+"\">"+a[1]+"</a></li>";
		},
		sep : function(a){
			return "<li><span class=\"ellipses\">"+a[0]+"</span></li>";
		}
	}  

	/***** 
	** FUNCTIONS 
	*****/	    

	this.getSettings = function(){
		return this._settings;
	};

	this.updateSettings = function(o){
		/*
			rewrites this._settings , this._S_ is left alone
		*/
		for (var attrname in o) { this._settings[attrname] = o[attrname]; }	
	};

	this.build = function(c){

		var s = ( c ) ? c : this._S_ ; /**/

		this.updateSettings(s);
		
		s = this.getSettings();

		
			var self     = this;
			var prev     = ( s.page - 1);
			var next     = ( s.page + 1);
			var lastpage = Math.ceil( s.total / s.limit );
			var lpm1     = ( lastpage - 1 );		
			var html = "";
			
			var firsttwo = "";
				firsttwo += renderTemplate(self ,'pageItem' , [ s.target , '1']);
				firsttwo += renderTemplate(self ,'pageItem' , [ s.target , '2']);

			var lasttwo = "";
				lasttwo += renderTemplate(self ,'pageItem' , [ s.target ,lpm1]);
				lasttwo += renderTemplate(self ,'pageItem' , [ s.target ,lastpage]);
			/** 
			* separator / ellipsis / placeholder 
			**/
			var sep = renderTemplate(self ,'sep' , [s.sep]);
			/* 
				LOGIC START 
				based on : http://www.strangerstudios.com/sandbox/pagination/diggstyle.php
			*/
			if(lastpage > 1){
				/**
				* not enough pages to bother breaking it up
				**/
				if ( lastpage < 7 + ( s.adj * 2) ){		
					for ( counter = 1; counter <= lastpage; counter++ ){
						if( counter == s.page ){
							html += renderTemplate(self ,'isSelected' , [ counter ]);
						}
						else{
							html += renderTemplate(self ,'pageItem' , [ s.target , counter ]);
						}				
					}
				}
				/**
				* enough pages to hide some
				**/
				else if( lastpage >= 7 + ( s.adj * 2 ) ){
					/* close to beginning; only hide later pages */
					if( s.page < 1 + ( s.adj * 3) ){
						for ( counter = 1; counter < 4 + ( s.adj * 2); counter++)
						{
							if ( counter == s.page){
								html += renderTemplate(self ,'isSelected' , [ counter ]);
							}
							else{
								html += renderTemplate(self ,'pageItem' , [ s.target , counter ]);
							}				
						}
						html += sep;
						html += lasttwo;
					}
					/*
						in middle; hide some front and some back 
					*/
					else if(lastpage - (s.adj * 2) > s.page && s.page > (s.adj * 2)){
						html += firsttwo;
						html += sep;
						for (counter = s.page - s.adj; counter <= s.page + s.adj; counter++)
						{
							if (counter == s.page){
								html += renderTemplate(self ,'isSelected' , [ counter ]);
							}
							else{
								html += renderTemplate(self ,'pageItem' , [ s.target , counter ]);
							}			
						}
						html += sep;
						html += lasttwo;	
					}
					/* 
						close to end; only hide early pages 
					*/
					else{
						html += firsttwo;
						html += sep;
						for (counter = lastpage - ( 1 + (s.adj * 3)); counter <= lastpage; counter++ )
						{
							if (counter == s.page){
								html += renderTemplate(self ,'isSelected' , [ counter ]);
							}
							else{
								html += renderTemplate(self ,'pageItem' , [ s.target , counter ]);				
							}
						}
					}				
				}
			}
			/* 
				LOGIC END 
			*/
			return html;

		
	};

	}
	return Paginathor;
} )( window );