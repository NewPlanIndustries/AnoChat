var client = {
	pageTitle:"",
	focused:true,
	_init:function(roomID)
	{
		$(document).ready(function(e) {
			room._init(roomID,function(){
				if (room.roomID == "") client.start();
				else if (room.active == false) client.error("Room is not active");
				else if (room.joined) client.main();
				else client.join();
			});
			client.pageTitle = document.title;
		});
		
		//inconsistent but sort of working
		$(window).focus(function(e) {
			client.focused = null;
			document.title = client.pageTitle;
		}).blur(function(e) {
			client.focused = 0;
		});
	},
	start:function(startName)
	{
		if (startName == undefined) startName = "";
		
		$("#startArea").fadeOut(fadeSpeed,function(){
			$(this).empty().append(makeElement("div",null,[
				makeElement("label",{"for":"startInput"},"Enter a name"),
				makeElement("form",{id:"startInputArea"},[
					makeElement("input",{type:"text","class":"niceInput",id:"startInput",value:startName}),
					makeElement("input",{type:"submit","class":"niceButton",id:"startButton",value:"Start"})
				]).submit(client.runStart),
				makeElement("p",{"class":"small"},"(not required)")
			])).fadeIn(fadeSpeed);
			
			setTimeout(function(){
				$("#startInput").focus();
			},fadeSpeed/2);
		});
	},
	runStart:function(e)
	{
		e.preventDefault();
		
		var startName = $("#startInput").val().trim();
		
		$("#startArea").fadeOut(fadeSpeed,function(){
			$(this).empty().append(makeElement("p",{align:"center"},[
				makeElement("img",{src:"/assets/images/loading.gif",width:100,height:100})
			])).fadeIn(fadeSpeed);
		});
		
		room.start(startName,function(data){
			if (data.status == false)
			{
				client.error(data,function(){
					client.start(startName);
				});
			}
			else
			{
				if (redirect("/"+room.roomID))
				{
					document.title = SITE_TITLE + " - Chat - " + room.roomID;
					client._init(room.roomID);
				}
			}
		});
	},
	join:function()
	{
		$("#chatContainer").fadeOut(fadeSpeed,function(){
			$(this).empty();
		});
		
		openModal({width:400,close:false},makeElement("div",null,[
			makeElement("h2",null,"Join Room"),
			makeElement("label",{"for":"joinInput"},"Enter your name"),
			makeElement("form",{id:"joinInputArea"},[
				makeElement("input",{type:"text","class":"niceInput",id:"joinInput"}),
				makeElement("input",{type:"submit","class":"niceButton",id:"joinButton",value:"Join"})
			]).submit(client.runJoin),
			makeElement("p",{"class":"small"},"(not required)")
		]));
		
		$("#joinInput").focus();
	},
	runJoin:function()
	{
		var name = $("#joinInput").val().trim();
		
		$("#joinInput").attr("disabled","disabled").blur();
		$("#joinButton").attr("disabled","disabled");
		
		room.join(name,function(data){
			if (room.joined)
			{
				closeModal();
				client.main();
			}
			else
			{
				client.error(data,function(){
					$("#joinInput").removeAttr("disabled").focus();
					$("#joinButton").removeAttr("disabled");
				});
			}
			
		});
		
		return false;
	},
	main:function()
	{
		$("#chatContainer").fadeOut(fadeSpeed,function(){
			$('#content').attr('valign','middle');
			$(this).empty().append(makeElement("div",{
				id:"chatBox"
			},[
				makeElement("div",{"class":"container",id:"chatLogContainer"},[
					makeElement("div",{id:"chatLogScroller"},[
						makeElement("div",{id:"chatLog"},[
							$.jStorage.get("snapchat_"+room.roomID,"")
						])
					])
				]),
				makeElement("div",{"class":"container",id:"userListContainer"},[
					makeElement("div",{id:"userListHeader",align:"center"},[
						makeElement("button",{"title":"Help"},[
							makeElement("img",{src:"/assets/images/help.png"})
						]).click(client.showHelp),
						
						// FUNCTIONALITY INCOMPLETE
						//makeElement("button",{"title":"Change Name",},[
						//	makeElement("img",{src:"/assets/images/edit.png"})
						//]).click(client.changeName),
						
						makeElement("button",{"title":"Send Invites"},[
							makeElement("img",{src:"/assets/images/mail.png"})
						]).click(client.invites),
						makeElement("button",{"title":"Leave Room"},[
							makeElement("img",{src:"/assets/images/close.png"})
						]).click(client.leave),
						makeElement("div",{"class":"clear"})
					]),
					makeElement("div",{id:"userListScroller"},[
						makeElement("div",{id:"userList"})
					])
				]),
				makeElement("form",{id:"chatInputArea"},[
					makeElement("input",{type:"text","class":"niceInput",id:"chatInput"}).checkField("#sendButton").focus(),
					makeElement("input",{type:"submit","class":"niceButton",id:"sendButton",value:"Send",disabled:"disabled"})
				]).submit(client.send)
			])).fadeIn(fadeSpeed,function(){
				room.startListening(client.listen);
				room.userList();
			});
			
			setTimeout(function(){
				$("#chatLogScroller").scrollTop($("#chatLog").height())
				$("#chatInput").focus();
			},fadeSpeed*.1);
		});
	},
	send:function()
	{
		var message = $("#chatInput").val().trim();
		
		if (message !== "") client.message(message);
		
		$("#chatInput").val("");
		return false;
	},
	message:function(message)
	{
		var messageBlock = client.addMessage({
			name:room.name,
			message:autolink(htmlentities(message,"ENT_NOQUOTES"))
		});
		
		room.send(message,function(data){
			trace(data);
			if (!data.status)
			{
				messageBlock.addClass('error').click(function(){
					$(this).remove();
					client.message(message);
				});
			}
		});
		
		
	},
	addMessage:function(data,type)
	{
		if (typeof client.focused == "number")
		{
			client.focused++;
			document.title = "(" + client.focused + ") " + client.pageTitle;
		}
		else document.title = client.pageTitle;
		
		var autoScroll = $("#chatLogScroller").scrollTop()+$("#chatLogScroller").height() >= $("#chatLog").height();
		
		var messageBlock = makeElement("p");
		if (type == "system")
		{
			messageBlock.append(makeElement("strong",null,data.message));
		}
		else
		{
			messageBlock.append(makeElement("strong",null,data.name + ": "));
			messageBlock.append(data.message);
			
			if (data.messageID != undefined) messageBlock.attr("id",data.messageID);
		}
		
		$("#chatLog").append(messageBlock);
		
		//REMOVE BAD MESSAGES?
		$.jStorage.set("snapchat_"+room.roomID,$('#chatLog').html());
		
		if (autoScroll) $("#chatLogScroller").scrollTop($("#chatLog").height());
		
		return messageBlock;
	},
	updateUserList:function(data)
	{
		var userList = makeElement("div");
		$.each(data,function(index,userData){
			userList.append(
				makeElement("div",
					{"title":userData.name+" (" + userData.status + ")","class":userData.status}
				,userData.name)
			)
		});
		
		$("#userList").empty().append(userList);
	},
	error:function(data,callback)
	{
		errorWindow(room.getErrorMessage(data),callback);
	},
	listen:function(data)
	{
		if (data.status == false)
		{
			client.error(data);
		}
		else
		{
			$.each(data.data,function(index,queueItem){
				trace(queueItem);
				
				switch(queueItem.action)
				{
					case "userList":
						client.updateUserList(queueItem.data);
						break;
					case "message":
					case "system":
						client.addMessage(queueItem.data,queueItem.action);
						break;
				}
			});
			
			if (!data.status) client.error(data);
		}
	},
	leave:function()
	{
		openModal({width:200},[
			makeElement("h2",null,"Leave Room?"),
			makeElement("div",{align:"center"},[
				makeElement("input",{type:"submit","class":"niceButton",id:"leaveButton",value:"Confirm"}).click(client.runLeave),
				makeElement("input",{type:"button","class":"niceButton red",id:"leaveCancel",value:"Cancel"}).click(closeModal)
			])
		]);
	},
	runLeave:function()
	{
		$.jStorage.deleteKey("snapchat_"+room.roomID);
		
		closeModal(function(){
			room.stopListening();
			$("#chatContainer").fadeOut(fadeSpeed,function(){
				$(this).empty().append(
					makeElement("p",{
						align:"center"
					},"Leaving Room&hellip;")
				).fadeIn(fadeSpeed,function(){
					room.leave(function(){
						openModal({width:300,close:false},[
							makeElement("h2",{align:"center"},"You Have Left The Room"),
							makeElement("div",{align:"center"},[
								makeElement("input",{type:"submit","class":"niceButton red",id:"leaveButton",value:"Okay"}).click(function(){
									closeModal(function(){
										client.join();
									});
								})
							])
						]);
						
						$('#leaveButton').focus();
					});
				});
			});
			
		});
	},
	changeName:function()
	{
		openModal({width:480},makeElement("div",{
			id:"changeNameContainer"
		},[
			makeElement("h2",null,"Change Name"),
			makeElement("label",{"for":"changeNameInput"},"Enter a new name"),
			makeElement("form",{id:"changeNameInputArea"},[
				makeElement("input",{type:"text","class":"niceInput",id:"changeNameInput"}),
				makeElement("input",{type:"submit","class":"niceButton",id:"changeNameButton",value:"Change"}),
				makeElement("input",{type:"button","class":"niceButton red",id:"changeNameCancel",value:"Cancel"}).click(closeModal)
			]).submit(client.runChangeName),
			makeElement("p",{"class":"small"},"(not required)")
		]));
		
		$("#changeNameInput").focus();
	},
	runChangeName:function()
	{
		var name = $("#changeNameInput").val().trim();
		
		$("#changeNameInput").attr("disabled","disabled").blur();
		$("#changeNameButton").attr("disabled","disabled");
		
		room.join(name,function(data){
			if (room.joined)
			{
				closeModal();
				$('#chatInput').focus();
			}
			else
			{
				client.error(data,function(){
					$("#changeNameInput").removeAttr("disabled").focus();
					$("#changeNameButton").removeAttr("disabled");
				});
			}
		});
		
		return false;
	},
	invites:function()
	{
		openModal({width:400},makeElement("div",null,[
			makeElement("h2",null,"Send Invites"),
			makeElement("label",{"for":"invitesInput"},"Enter email addresses separated by commas"),
			makeElement("form",{id:"inviteInputArea"},[
				makeElement("input",{type:"text","class":"niceInput",id:"invitesInput"}).checkField("#invitesButton"),
				makeElement("input",{type:"submit","class":"niceButton",id:"invitesButton",value:"Send",disabled:"disabled"})
			]).submit(client.sendInvites)
		]));
		
		$("#invitesInput").focus();	
	},
	sendInvites:function()
	{
		var emails = $("#invitesInput").val().trim().split(",");
		
		$("#invitesInput").attr("disabled","disabled").blur();
		$("#invitesButton").attr("disabled","disabled");
		
		var validEmails = [];
		$.each(emails,function(index,email){
			email = $.trim(email);
			if (validEmail(email)) validEmails.push(email);
		});
		
		if (validEmails.length == 0)
		{
			client.error("Please enter at least one email address",function(){
				$("#invitesInput").removeAttr("disabled").focus();
				$("#invitesButton").removeAttr("disabled");
			});
		}
		else
		{
			closeModal();
			$('#chatInput').focus();
			room.invite(validEmails);
		}
		
		return false;
	},
	showHelp:function()
	{
		openModal({width:800},makeElement("div",null,[
			makeElement("iframe",{
				src:"/help/true",
				frameborder:0,
				width:"100%",
				height:600
			}),
			/*
			makeElement("h2",null,"Report a Bug"),
			makeElement("a",{target:"_blank",href:"/bugs"},"Here")
			*/
		]));
	}
};
