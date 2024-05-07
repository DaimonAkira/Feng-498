const ip = "0.0.0.0"; // this is my local ip
const port = "9090";
const urll = 'ws://'+ip+':'+port;
const rostop = "/cmd_vel";

let brakestatus = false;

statuscontrol = function(){
  const statusElement = document.getElementById("status");
  if (statusElement.innerHTML == "Connected"){
    statusElement.style.color="green";
  } else if (statusElement.innerHTML == "Error"){
    statusElement.style.color="red";
  } else {
    statusElement.style.color="purple";
  }
}

/*var ros = new ROSLIB.Ros({
  url: urll
});*/

var ros = new ROSLIB.Ros({
    //url: 'ws://192.168.1.21:9090'
    url: urll
  });

ros.on('connection', function () {
  const statusElement = document.getElementById("status");
  statusElement.innerHTML = "Connected";
  statuscontrol();
  console.log("Ros connection established to " + urll);
});

ros.on('error', function (error) {
  const statusElement = document.getElementById("status");
  statusElement.innerHTML = "Error";
  statuscontrol();
  console.log("Something went wrong at " + urll);
});

ros.on('close', function () {
  const statusElement = document.getElementById("status");
  statusElement.innerHTML = "Closed";
  statuscontrol();
  console.log("Ros connection closed at " + urll);
});

/*var txt_listener = new ROSLIB.Topic({
  ros: ros,
  name: '/txt_msg',
  messageType: 'std_msgs/String'
});*/

/*txt_listener.subscribe(function (m) {
  document.getElementById("msg").innerHTML = m.data;
  move(1, 0);
});*/

cmd_vel_listener = new ROSLIB.Topic({
  ros: ros,
  name: rostop,
  messageType: 'geometry_msgs/Twist'
});

/*cmd_camera_listener = new ROSLIB.Topic({
  ros: ros,
  name: "/eva_mars/camera_port_joint_position_controller/command",
  messageType: 'std_msgs/Float64'
});*/

move = function (linear, angular) {
  var twist = new ROSLIB.Message({
    linear: {
      x: linear,
      y: 0,
      z: 0
    },
    angular: {
      x: 0,
      y: 0,
      z: angular
    }
  });
  cmd_vel_listener.publish(twist);
}

createJoystick = function () {
  var options = {
    zone: document.getElementById('zone_joystick'),
    threshold: 0.1,
    position: { left: '15%', top: '38%' },
    mode: 'static',
    size: 150,
    color: '#000000',
  };

  manager = nipplejs.create(options);

  linear_speed = 0;
  angular_speed = 0;

  manager.on('start', function (event, nipple) {
    timer = setInterval(function () {
      move(linear_speed, angular_speed);
    }, 25);
  });

  manager.on('move', function (event, nipple) {
    max_linear = 1.0; // m/s
    max_angular = 0.5; // rad/s
    max_distance = 75.0; // pixels;
    linear_speed = Math.sin(nipple.angle.radian) * max_linear * nipple.distance/max_distance;
    angular_speed = -Math.cos(nipple.angle.radian) * max_angular * nipple.distance/max_distance;
  });

  manager.on('end', function () {
    if (timer) {
      clearInterval(timer);
    }
    move(0, 0);
  });
}

function todayDate(){
  var d = new Date();
  var n = d.getFullYear() + "  ";
  return document.getElementById("copydate").innerHTML = n;
}

window.onload = function () {
  createJoystick();
}
