/**
 *	Version: 0.1
 */
(function($) {
	$.icndb = {};
	$.icndb.client = {}
	$.icndb.client.id = 4;
	$.icndb.client.version = 0.1;

	var base = "http://api.icndb.com/";

	/**
	 *	Returns the full URL of the given resource.
	 *	Ex.: 'jokes/random/5' -> http://api.icndb.com/jokes/random/5?client=4&clientVersion=0.1
	 *
	 *	@param	resource	The relative path of hte resource, NO LEADING '/'
	 */
	var full = function(resource) {
		return base + resource + '?client=' + $.icndb.client.id + '&clientVersion=' + $.icndb.client.version;
	};

	/**
	 *	Calls the URL, evaluated the JSON returned and returns the result value as JS object on success.
	 *	No exceptions here, this always uses script communication.
	 *
	 *	@param	destination			Location of the destination (String URL)
	 *	@param	successCB(result)	Callback on success. Will be called with result value as JS object.
	 *								result = {"type": <type:String>, "value": <value:Object>}
	 */
	var callServer = function(destination, successCB, errorCB) {
		$.ajax({
			url: destination,
			dataType: "jsonp",
			type: "GET",
			success: function(result) {
				successCB(result);
			}
		});
	}

	/************************************************************************
	 *	Simple API
	 ************************************************************************/

	/**
	 *	Returns multiple random Chuck Norris jokes to the callback function, optionally with given first name and last name.
	 *	There can be no error when retrieving random jokes.
	 *
	 *	@param	success(jokes: [{id: <id:integer>, joke: <joke:String>}])
	 *
	 *	OR
	 *
	 *	@param	{
	 *		success: function(jokes: [{id: <id:integer>, joke: <joke:String>}])
	 *		number		[optional]	The number of jokes to retrieve. If not given, 1 joke is retrieved.
	 *		firstName	[optional] 	The first name of the main character in the joke.
	 *		lastName	[optional] 	The last name of the main character in the joke.
	 *		limitTo		[optional] 	An array of categories (Strings) to which the joke may belong.
	 *		exclude	[optional, only processed if limitTo not given]	An array of categories (Strings) to which the joke may not belong.
	 *	}
	 */
	$.icndb.getRandomJokes = function(args) {
		var success = function(result) {
			// notice: never NoSuchJokeException with random jokes
			if(args.success) {
				args.success(result.value);
			} else {
				args(result.value);
			}
		}
		var number = 1;
		if(args.number) {
			number = args.number;
		}
		var url = full("jokes/random/" + number + '?escape=javascript');
		if(args.firstName) {
			url += "&firstName=" + args.firstName;
		}
		if(args.lastName) {
			url += "&lastName=" + args.lastName;
		}
		if(args.limitTo) {
			url += "&limitTo=[" + args.limitTo.toString() + "]";
		} else if(args.exclude) {
			url += "&exclude=[" + args.exclude.toString() + "]";
		}
		callServer(url, success, function() {} );
	};

	/**
	 *	Returns a random Chuck Norris joke to the callback function, optionally with given first name and last name.
	 *	There can be no error when retrieving a random joke.
	 *
	 *	@param	success(joke: {id: <id:integer>, joke: <joke:String>})
	 *
	 *	OR
	 *
	 *	@param	{
	 *		success: function(joke: {id: <id:integer>, joke: <joke:String>})
	 *		firstName	[optional] The first name of the main character in the joke.
	 *		lastName	[optional] The last name of the main character in the joke.
	 *		limitTo		[optional] An array of categories (Strings) to which the joke may belong.
	 *		exclude	[optional, only processed if limitTo not given]	An array of categories (Strings) to which the joke may not belong.
	 *	}
	 */
	$.icndb.getRandomJoke = function(args) {
		var args2 = {};
		$.extend(args2, args);
		args2.success = function(result) {
			// notice: never NoSuchJokeException with random jokes
			result = result[0];
			if(args.success) {
				args.success(result);
			} else {
				args(result);
			}
		};
		args2.number = 1;
		$.icndb.getRandomJokes(args2);
	};

	/**
	 *	Returns all the jokes in the database.
	 *
	 *	@param	success: function(jokes: [{id: <id:integer>, joke: <joke:String>}])
	 *
	 *	OR
	 *
	 *	@param	{
	 *		success: function(jokes: [{id: <id:integer>, joke: <joke:String>}])
	 *		firstName	[optional] The first name of the main character in the joke.
	 *		lastName	[optional] The last name of the main character in the joke.
	 *		limitTo		[optional] An array of categories (Strings) to which the joke may belong.
	 *		exclude	[optional, only processed if limitTo not given]	An array of categories (Strings) to which the joke may not belong.
	 */
	$.icndb.getJokes = function(args) {
		var success = function(result) {
			// notice: never NoSuchJokeException when retrieving all jokes
			if(args.success) {
				args.success(result.value);
			} else {
				args(result.value);
			}
		}
		var url = full("jokes");
		if(args.firstName) {
			url += "&firstName=" + args.firstName;
		}
		if(args.lastName) {
			url += "&lastName=" + args.lastName;
		}
		if(args.limitTo) {
			url += "&limitTo=[" + args.limitTo.toString() + "]";
		} else if(args.exclude) {
			url += "&exclude=[" + args.exclude.toString() + "]";
		}
		callServer(url, success, function() {} );
	};

	/**
	 *	Returns the categories in the systems as an array of strings.
	 *
	 *	@param	callback:function(categories:[String])
	 */
	$.icndb.getCategories = function(callback) {
		var success = function(result) {
			callback(result.value);
		};
		var url = full("categories");
		callServer(url, success, function() {} );
	}

	/**
	 *	Returns the number of jokes in the database.
	 *
	 *	@param	callback:function(categories:[integer])
	 */
	$.icndb.getNumberOfJokes = function(callback) {
		var success = function(result) {
			callback(result.value);
		};
		var url = full("jokes/count");
		callServer(url, success, function() {} );
	}
})(jQuery);
