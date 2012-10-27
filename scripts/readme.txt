
The stomp-listener.php script is an implementaion of an stomp listener wich
executes Amun_Stomp_Listener_* classes wich are added to the stomp manager. If
the broker sends an stomp frame the manager calls the run method of each
listener. Note because php has no threading functionality every listener is
executed sequentially that means only if a listener has finished execution the
next listener is called. Because of this downside I have plans to write an
listener in java wich executes every listener concurrent in its own thread. Also
sending xmpp messages etc. is much better in java.

In order to publish notifications to the broker you have to add the
Amun_Notify_Stomp class to the table "amun_system_notify".

Note all clases uses the php stomp extensions wich must be installed. See
http://php.net/manual/en/book.stomp.php for more informations. The stomp
listener was tested with apollo an messaging queue based on active mq and
optimized for stomp. More informations about apollo at
http://activemq.apache.org/apollo/

