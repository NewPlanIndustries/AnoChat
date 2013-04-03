var room = {
	name:'',
	roomID:false,
	active:false,
	joined:false,
	_init:function(roomID,callback)
	{
		if (roomID == undefined) roomID = false;
		
		if (roomID === false) $.error('roomID cannot be empty');
		room.roomID = roomID;
		
		if (roomID !== "")
		{
			room.checkSocket(function(){
				room.checkJoin(callback);
			});
		}
		else callback();
	},
	runAction:function(action,data,callback)
	{
		if (room.roomID === false) $.error('roomID cannot be empty');
		
		var now = new Date();
		var url = "/rooms/" + action + "/" + room.roomID + "?" + now.getTime();
		
		return $.ajax({
			type: 'POST',
			dataType: 'json',
			url: url,
			data: {
				data: data
			},
			success: function(data)
			{
				room.processAction(data,callback);
			},
			error:function(jqXHR)
			{
				if (jqXHR.statusText == "abort") return;
				
				room.processAction({
					"status":false,
					"message":"Unable to process request."
				},callback);
			}
		});
	},
	processAction:function(data,callback)
	{
		if (typeof data == 'string')
		{
			data = JSON.parse(data);
		}
		
		if (callback !== undefined && typeof callback == "function")
		{
			callback(data);
		}
	},
	checkSocket:function(callback)
	{
		room.runAction('checkSocket',false,function(data){
			room.active = data.status;
			callback(data);
		});
	},
	checkJoin:function(callback)
	{
		room.runAction('checkJoin',{
			throw:false,
			confirm:true
		},function(data){
			trace(data);
			room.joined = data.status;
			if (room.joined) room.name = data.data;
			callback(data);
		});
	},
	start:function(name,callback)
	{
		room.runAction('start',{
			"name":name
		},function(data){
			if (data.status) room.roomID = data.data;
			callback(data);
		});
	},
	join:function(name,callback)
	{
		room.runAction('join',{
			"name":name
		},function(data){
			room.joined = data.status
			room.name = room.joined ? data.data : name;
			callback(data);
		});
	},
	leave:function(callback)
	{
		room.runAction('leave',null,callback);
	},
	send:function(message,callback)
	{
		var messageID = room.userID + "_" + (new Date()).getTime();
		
		room.runAction('send',{
			"message":message,
			"messageID":messageID
		},callback);
		
		return messageID;
	},
	userList:function(callback)
	{
		room.runAction('userList',null,callback);
	},
	listeningRequest:null,
	startListening:function(callback)
	{
		room.listeningRequest = room.runAction('listen',null,function(data){
			room.listeningRequest = null;
			if (data.status) room.startListening(callback);
			callback(data);
		});
	},
	stopListening:function()
	{
		if (room.listeningRequest == null) return;
		room.listeningRequest.abort();
	},
	getErrorMessage:function(data)
	{
		var errorMessage = "No Error Message Found";
		if (data !== undefined)
		{
			if (typeof data == "string")
			{
				errorMessage = data;
			}
			else if (data.message !== undefined)
			{
				errorMessage = data.message;
			}
			else if (data.data !== undefined && data.data.message !== undefined)
			{
				errorMessage = data.data.message;
			}
		}
		
		return errorMessage;
	},
	invite:function(emails,callback)
	{
		room.runAction('invite',{
			emails:emails
		},callback);
	}
};