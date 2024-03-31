/*
  Tahsin Can Koçum
*/


const ip = "192.168.1.21";
const port = ":9090";
const urll = 'ws://'+ip+port;


GOAL_X = 0.0
GOAL_Y = 0.0
ROBOT_POSE_X = 0.0
ROBOT_POSE_Y = 0.0
ROBOT_POSE_ORIENTATION = 0.0



var ros = new ROSLIB.Ros({
    //url: 'ws://192.168.1.21:9090'
    url: urll
  });

  var odom_listener = new ROSLIB.Topic({
    ros: ros,
    name: '/odom',
    messageType: 'nav_msgs.msg/Odometry'
  });

  odom_listener.subscribe(function (m) {
    odom = m.data;
  });
  
  cmd_veltarget_listener = new ROSLIB.Topic({
    ros: ros,
    name: "/cmd_vel",
    messageType: 'geometry_msgs/Twist'
  });
    odom_callback = function(odom){
    
    global ROBOT_POSE_ORIENTATION, ROBOT_POSE_X, ROBOT_POSE_Y

    //Yaw calculations
    orientation_q = odom.pose.pose.orientation
    orientation_list = [orientation_q.x, orientation_q.y, orientation_q.z, orientation_q.w]

    (roll, pitch, yaw) = euler_from_quaternion(orientation_list)

    ROBOT_POSE_ORIENTATION = yaw
    ROBOT_POSE_X = odom.pose.pose.position.x
    ROBOT_POSE_Y = odom.pose.pose.position.y
    }
    appVel = function (g_x,g_y){

    msg = Twist()
    goal = Point()
    goal.x = g_x
    goal.y = g_y
        
    inc_x = goal.x - ROBOT_POSE_X
    inc_y = goal.y - ROBOT_POSE_Y
    angle_to_goal = math.atan2(inc_y, inc_x)

    if abs(angle_to_goal - ROBOT_POSE_ORIENTATION) > 0.1:
        msg.linear.x = 0.0
        msg.angular.z = 0.5
    else:
        msg.linear.x = 0.1
        msg.angular.z = 0.0
    return msg
    }
    movetotarget = function(){

    global GOAL_X, GOAL_Y

    
    cmd_vel_pub = rospy.Publisher("cmd_vel", Twist, queue_size=1)
    odom_sub = rospy.Subscriber("odom", Odometry, odom_callback)

    r = rospy.Rate(4)
    msg = Twist()

    GOAL_X = float(input("Enter goal pos x: "))
    GOAL_Y = float(input("Enter goal pos y: "))

    while not rospy.is_shutdown():
    
        msg = appVel(GOAL_X,GOAL_Y)
        cmd_vel_pub.publish(msg)
   }